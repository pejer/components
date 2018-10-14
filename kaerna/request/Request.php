<?php
/**
 * Created by PhpStorm.
 * User: henrikpejer
 * Date: 2018-09-24
 * Time: 07:20
 */

namespace DHP\kaerna\request;


use DHP\kaerna\interfaces\RequestInterface;
use DHP\kaerna\Module;

class Request extends Module implements RequestInterface
{

    /**
     * @var string
     */
    private $method;
    /**
     * @var null
     */
    private $uri;
    /**
     * @var null
     */
    private $body;
    /**
     * @var array
     */
    private $post;
    /**
     * @var array
     */
    private $get;
    /**
     * @var array
     */
    private $files;
    /**
     * @var array
     */
    private $headers;

    /**
     * @param string $method method of the request
     * @param null $uri the uri of the request
     * @param null $body body of the request
     * @param array $post post variables of the request
     * @param array $get get variables of the request
     * @param array $files files sent in the request
     * @param array $headers headers sent in the request
     */
    public function __construct(
        $method = "HEADER",
        $uri = null,
        $body = null,
        $post = array(),
        $get = array(),
        $files = array(),
        $headers = array()
    ) {
        $this->method  = $method;
        $this->uri     = $uri;
        $this->body    = $body;
        $this->post    = $post;
        $this->get     = $get;
        $this->files   = $files;
        $this->headers = $headers;
    }

    public static function createFromEnvironment()
    {
        $method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : null;
        switch (true) {
            case isset($_SERVER['PATH_INFO']):
                $uri = $_SERVER['PATH_INFO'];
                break;
            case isset($_SERVER['SCRIPT_NAME']):
                $uri = dirname($_SERVER['SCRIPT_NAME']);
                break;
            default:
                $uri = '';
                break;
        }
        $body               = file_get_contents('php://input');
        $fstat_input_stream = fstat(STDIN);
        if ($fstat_input_stream['size'] > 0) {
            $body = \stream_get_contents(STDIN, $fstat_input_stream['size']);
        }
        $post    = $_POST;
        $get     = $_GET;
        $files   = $_FILES;
        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (strpos($key, 'HTTP_') === 0) {
                $headers[$key] = $value;
            }
        }
        return new self($method, $uri, $body, $post, $get, $files, $headers);
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @return null
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * @return null
     */
    public function getBody()
    {
        return $this->body;
    }

    public function setBody($body)
    {
        $that       = clone $this;
        $that->body = $body;
        return $that;
    }

    /**
     * @return array
     */
    public function getPost(): array
    {
        return $this->post;
    }

    /**
     * @return array
     */
    public function getGet(): array
    {
        return $this->get;
    }

    /**
     * @return array
     */
    public function getFiles(): array
    {
        return $this->files;
    }

    /**
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }
}