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
     * @param string $method method of the request
     * @param null   $uri the uri of the request
     * @param null   $body body of the request
     * @param array  $post post variables of the request
     * @param array  $get get variables of the request
     * @param array  $files files sent in the request
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

    /**
     * Merges post and get vars to one
     */
    private function makeVars()
    {
        $this->requestVars = array_merge($this->requestGet, $this->requestPost);
    }

    /**
     * @param $name
     * @return array|null|String
     */
    public function __get($name)
    {
        $return  = null;
        $getName = $this->readAndSettableVars($name);
        if (isset($getName)) {
            $return = $this->{$getName};
        }
        return $return;
    }

    /**
     * Magically sets parameter
     *
     * @param $name
     * @param $value
     * @return bool
     */
    public function __set($name, $value)
    {
        $return  = null;
        $getName = $this->readAndSettableVars($name);
        if (isset($getName)) {
            $this->{$getName} = $value;
        }
        return true;
    }

    /**
     * Will return the internal variable name, use when trying to set or get
     * a variable
     *
     * @param $varToFetch
     * @return string
     */
    private function readAndSettableVars($varToFetch)
    {
        switch (strtolower($varToFetch)) {
            case 'get':
                $return = 'requestGet';
                break;
            case 'post':
                $return = 'requestPost';
                break;
            case 'files':
                $return = 'requestFiles';
                break;
            case 'headers':
                $return = 'requestHeaders';
                break;
            case 'uri':
                $return = 'requestUri';
                break;
            case 'body':
                $return = 'requestBody';
                break;
            case 'method':
                $return = 'requestMethod';
                break;
            case 'variables':
                $return = 'requestVars';
                break;
            default:
                $return = null;
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
        $this->requestBody    = file_get_contents('php://input');
        switch (true) {
            case isset($_SERVER['REQUEST_URI']):
                $url = $_SERVER['REQUEST_URI'];
                break;
            case isset($_SERVER['argv']) && isset($_SERVER['argv'][1]):
                $url    = $_SERVER['argv'][1];
                $this->requestMethod = isset($_SERVER['argv'][2]) ? $_SERVER['argv'][2] : 'HEADER';
                $this->requestBody   = isset($_SERVER['argv'][3]) ? $_SERVER['argv'][3] : $this->requestBody;
                break;
            default:
                $url = '';
        }
        $this->requestUri = $this->cleanUri(parse_url($url, PHP_URL_PATH));
        if (isset($_SERVER['REQUEST_METHOD'])) {
            $this->requestMethod = $_SERVER['REQUEST_METHOD'];
        }

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
}
