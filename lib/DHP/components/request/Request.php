<?php
namespace DHP\components\request;

/**
 *
 * Created by: Henrik Pejer, mr@henrikpejer.com
 * Date: 2014-02-07 13:01
 *
 */

/**
 * Class Request
 *
 * Wrapps values and variables in a requests
 *
 * @package DHP\components\request
 *
 * @property-read string method
 * @property-read string uri
 * @property-read string body
 * @property-read array  post
 * @property-read array  get
 * @property-read array  files
 * @property-read array  headers
 * @property-read array  variables

 */
class Request
{
    private $requestMethod;
    private $requestUri;
    private $requestBody;
    private $requestPost;
    private $requestGet;
    private $requestFiles;
    private $requestHeaders;
    private $requestVars;

    /**
     * @param string $method  method of the request
     * @param null   $uri     the uri of the request
     * @param null   $body    body of the request
     * @param array  $post    post variables of the request
     * @param array  $get     get variables of the request
     * @param array  $files   files sent in the request
     * @param array  $headers headers sent in the request
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
        $this->requestMethod  = $method;
        $this->requestUri     = $this->cleanUri($uri);
        $this->requestPost    = $post;
        $this->requestGet     = $get;
        $this->requestFiles   = $files;
        $this->requestHeaders = $headers;
        $this->requestBody    = $body;
        $this->makeVars();
    }

    /**
     * @param $name
     * @return array|null|String
     */
    public function __get($name)
    {
        $return = null;
        switch (strtolower($name)) {
            case 'get':
                $return = $this->requestGet;
                break;
            case 'post':
                $return = $this->requestPost;
                break;
            case 'files':
                $return = $this->requestFiles;
                break;
            case 'headers':
                $return = $this->requestHeaders;
                break;
            case 'uri':
                $return = $this->requestUri;
                break;
            case 'body':
                $return = $this->requestBody;
                break;
            case 'method':
                $return = $this->requestMethod;
                break;
            case 'variables':
                $return = $this->requestVars;
                break;
        }
        return $return;
    }


    /**
     * This uses whatever it can find from the environment and tries to set
     * up the class automatically
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function setupWithEnvironment()
    {
        switch (true) {
            case isset( $_SERVER['REQUEST_URI'] ):
                $url = $_SERVER['REQUEST_URI'];
                break;
            case isset( $_SERVER['argv'] ) && isset( $_SERVER['argv'][1] ):
                $url = $_SERVER['argv'][1];
                break;
            default:
                $url = '';
        }
        $this->requestUri = $this->cleanUri(parse_url($url, PHP_URL_PATH));
        if (isset( $_SERVER['REQUEST_METHOD'] )) {
            $this->requestMethod = $_SERVER['REQUEST_METHOD'];
        }

        $this->requestBody    = file_get_contents('php://input');
        $this->requestPost    = $_POST;
        $this->requestGet     = $_GET;
        $this->requestFiles   = $_FILES;
        $this->requestHeaders = array();
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $this->requestHeaders[str_replace(
                    ' ',
                    '-',
                    ucwords(strtolower(str_replace('_', ' ', substr($name, 5))))
                )] = $value;
            }
        }
        $this->makeVars();
    }

    /**
     * Merges post and get vars to one
     */
    private function makeVars()
    {
        $this->requestVars = array_merge($this->requestGet, $this->requestPost);
    }

    /**
     * We trim of spaces and slashes from the uri so we never have beginning or
     * trailing slashes
     *
     * @param string $uri
     * @return string
     */
    private function cleanUri($uri)
    {
        return trim($uri, ' /');
    }
}
