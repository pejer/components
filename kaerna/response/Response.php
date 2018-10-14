<?php
/**
 * Created by PhpStorm.
 * User: henrikpejer
 * Date: 2018-09-24
 * Time: 07:21
 */

namespace DHP\kaerna\response;

use DHP\kaerna\interfaces\ResponseInterface;
use DHP\kaerna\interfaces\RequestInterface;
use DHP\kaerna\Module;

const STATUS_HEADERS = [
    "100" => "Continue",
    "101" => "Switching Protocols",
    "200" => "OK",
    "201" => "Created",
    "202" => "Accepted",
    "203" => "Non-Authoritative Information",
    "204" => "No Content",
    "205" => "Reset Content",
    "206" => "Partial Content",
    "300" => "Multiple Choices",
    "301" => "Moved Permanently",
    "302" => "Found (HTTP/1.1)",
    "303" => "See Other (HTTP/1.1)",
    "304" => "Not Modified",
    "305" => "Use Proxy",
    "306" => "",
    "307" => "Temporary Redirect",
    "308" => "Permanent Redirect",
    "400" => "Bad Request",
    "401" => "Unauthorized",
    "405" => "Method Not Allowed",
    "406" => "Not Acceptable",
    "407" => "Proxy Authentication Required",
    "408" => "Request Timeout",
    "409" => "Conflict",
    "410" => "Gone",
    "411" => "Length Required",
    "412" => "Precondition Failed",
    "413" => "Payload Too Large",
    "414" => "Request - URI Too Long",
    "415" => "Unsupported Media Type",
    "416" => "Requested Range Not Satisfiable",
    "417" => "Expectation Failed",
    "418" => "I'm a teapot",
    "500" => "Internal Server Error",
    "501" => "Not Implemented",
    "502" => "Bad Gateway",
    "503" => "Service Unavailable",
    "504" => "Gateway Timeout",
    "505" => "HTTP Version Not Supported",
    "509" => "Bandwidth Limit Exceeded"
];


class Response extends Module implements ResponseInterface
{

    private $attributes = [];
    private $headers    = [];
    /**
     * @var RequestInterface
     */
    private $request;

    /** @var mixed */
    private $body;

    /** @var bool if headers have been sent or not */
    private $headersSent = false;

    public function __construct(RequestInterface $request)
    {
        $this->request = $request;
    }

    # todo: Actually make it a bit smrter, do'h
    public function send()
    {
        $this->sendHeaders();
        $this->sendBody();
    }

    public function appendBody(string $body)
    {
    }

    public function getBody()
    {
    }

    /**
     * Sets the body
     *
     * Todo: how to handle things like files, streams etc...?
     * @param mixed $body
     */
    public function setBody($body)
    {
        $this->body = $body;
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    public function setHeader(string $name, string $value, int $httpStatus = null)
    {
        $this->headers[$name] = ['value' => $value, 'status' => $httpStatus];
        return $this;
    }

    public function setStatus(int $statusNumber, string $statusMessage = null)
    {
        if (!isset($statusMessage)) {
            $statusMessage = $this->headers[$statusNumber];
        }
        $this->setHeader($statusNumber, $statusMessage, $statusNumber);
    }

    public function setAttribute(string $name, $value)
    {
        return $this->setAttributes([$name => $value]);
    }

    public function __get($name)
    {
        $ret = $this->getAttribute($name, \null);
        return $ret;
    }

    public function getAttribute(string $name, $default = \null)
    {
        return $this->getAttributes([$name])[$name];
    }


    public function getAttributes(array $names, $default = \null): array
    {
        $return = [];
        foreach ($names as $key) {
            $return[$key] = isset($this->attributes[$key]) ? $this->attributes[$key] : $default;
        }
        return $return;
    }

    public function setAttributes(array $attributes)
    {
        $that             = clone $this;
        $that->attributes = $attributes + $that->attributes;
        return $that;
    }

    private function sendBody()
    {
    }

    private function sendHeaders()
    {

        foreach ($this->headers as $header => $values) {
            $headerString = $header;
            if (!empty($values['value'])) {
                $headerString .= ': ' . $values['value'];
            }
            header($headerString, false, $values['status']);
        }
        $this->headersSent = true;
    }

}