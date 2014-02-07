<?php
namespace DHP\components\request;

/**
 *
 * Created by: Henrik Pejer, mr@henrikpejer.com
 * Date: 2014-02-07 13:01
 *
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
        $this->requestUri     = $uri;
        $this->requestPost    = $post;
        $this->requestGet     = $get;
        $this->requestFiles   = $files;
        $this->requestHeaders = $headers;
        $this->requestBody    = $body;
        $this->makeVars();
    }

    /**
     * Returns the merged GET and POST variables.
     *
     * Please note that if GET and POST share the same key name, then POST will overwrite
     * the GET value.
     *
     * Example:
     * $_GET['something'] = 1;
     * $_POST['something'] = 2;
     *
     * $this->variables() will return array('something' => 2);
     *
     * @return array
     */
    public function variables()
    {
        return $this->requestVars;
    }

    /**
     * Returns the files array
     * @return array
     */
    public function files()
    {
        return $this->requestFiles;
    }

    /**
     * Returns the http-headers
     * @return array
     */
    public function headers()
    {
        return $this->requestHeaders;
    }
    /**
     * Returns $_GET values
     *
     * @return array
     */
    public function get()
    {
        return $this->requestGet;
    }

    /**
     * Returns $_POST - values
     *
     * @return array
     */
    public function post()
    {
        return $this->requestPost;
    }
    /**
     * Returns the URI
     *
     * @return null|String
     */
    public function uri()
    {
        return $this->requestUri;
    }

    /**
     * Returns the method of the request
     *
     * @return string
     */
    public function method()
    {
        return $this->requestMethod;
    }

    /**
     * returns the body of the request
     *
     * @return null|String
     */
    public function body()
    {
        return $this->requestBody;
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
        if (isset( $_SERVER['REQUEST_URI'] )) {
            $this->requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        }
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
}
