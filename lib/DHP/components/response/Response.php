<?php
/**
 * Created by Henrik Pejer ( mr@henrikpejer.com )
 * Date: 2014-02-05 21:56
 */

namespace DHP\components\response;

/**
 * Class Response
 *
 * @package DHP\components
 * @author  Henrik Pejer
 *
 * This class will handle the response that is supposed to be sent
 * back to the client.
 */
class Response
{

    /** @var array List of common status headers */
    private $headerStatusCodes = array(
      100 => "Continue",
      101 => "Switching Protocols",
      200 => "OK",
      201 => "Created",
      202 => "Accepted",
      203 => "Non-Authoritative Information",
      204 => "No Content",
      205 => "Reset Content",
      206 => "Partial Content",
      300 => "Multiple Choices",
      301 => "Moved Permanently",
      302 => "Found",
      303 => "See Other",
      304 => "Not Modified",
      305 => "Use Proxy",
      307 => "Temporary Redirect",
      400 => "Bad Request",
      401 => "Unauthorized",
      402 => "Payment Required",
      403 => "Forbidden",
      404 => "Not Found",
      405 => "Method Not Allowed",
      406 => "Not Acceptable",
      407 => "Proxy Authentication Required",
      408 => "Request Time-out",
      409 => "Conflict",
      410 => "Gone",
      411 => "Length Required",
      412 => "Precondition Failed",
      413 => "Request Entity Too Large",
      414 => "Request-URI Too Large",
      415 => "Unsupported Media Type",
      416 => "Requested range not satisfiable",
      417 => "Expectation Failed",
      500 => "Internal Server Error",
      501 => "Not Implemented",
      502 => "Bad Gateway",
      503 => "Service Unavailable",
      504 => "Gateway Time-out",
      505 => "HTTP Version not supported"
    );

    /** @var array header store */
    private $headers = array();

    /** @var null stores the data to be sent */
    private $body = null;

    /** @var bool if we have sent headers or not */
    private $headersSent = false;

    /**
     *
     * This method sets the headers that is to be sent to the client
     *
     * @param String       $value          header data
     * @param boolean      $replace        if we should replace a previous header, set to false to NOT replace header
     * @param integer|null $httpStatusCode The int status code for this header
     *
     * @throws \RuntimeException
     * @return bool
     */
    public function setHeader($value, $replace = true, $httpStatusCode = null)
    {
        if ($this->headersSent) {
            throw new \RuntimeException("Headers have already been sent");
        }
        $headerData = $this->formatHeaderData($value);
        if ($replace === false && isset( $this->headers[$headerData] )) {
            return $this;
        }
        $this->headers[$headerData] = $httpStatusCode;
        return $this;
    }

    /**
     *
     * Return headers already set
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     *
     * Returns the data that is to be sent to the client
     *
     * @return null|String|Array
     */
    public function getBody()
    {
        return $this->renderBody();
    }

    /**
     *
     * Sets the data that is to be sent to the client
     *
     * @param String|Array $data the data to be sent to the client
     *
     * @return $this
     */
    public function setBody($data)
    {
        $this->body = $data;
        return $this;
    }

    /**
     *
     * Appends data to existing body data
     *
     * @param $data
     *
     * @return $this
     * @throws \RuntimeException when data-types missmatch
     */
    public function appendBody($data)
    {
        if (empty( $this->body )) {
            return $this->setBody($data);
        } else {
            # if data is object/array OR $this->body is object/array = they must match that...
            $dataType = gettype($data);
            $bodyType = gettype($this->body);
            switch (true) {
                case in_array($dataType, array('object', 'array')) && !in_array($bodyType, array('object', 'array')):
                case !in_array($dataType, array('object', 'array')) && in_array($bodyType, array('object', 'array')):
                    throw new \RuntimeException("To be able to append data - data must be of same type");
                    break;
            }
        }
        if (in_array($dataType, array('object', 'array'))) {
            $this->body = (array) $this->body + (array) $data;
        } else {
            $this->body .= $data;
        }
        return $this;
    }

    /**
     * Sends the headers
     */
    public function sendHeaders()
    {
        $this->headersSent = true;
        foreach ($this->headers as $headerValue => $statusCode) {
            \header($headerValue, true, $statusCode);
        }
    }

    /**
     * Sends the body
     * @return $this
     */
    public function sendBody()
    {
        echo $this->renderBody();
        return $this;
    }

    /**
     * Sends headers and body
     * @return $this
     */
    public function send(){
        $this->sendHeaders();
        $this->sendBody();
        return $this;
    }

    /**
     * Sets the http-status for the request
     *
     * @param int         $statusCode    The http status code ie 200, 404, 500 etc
     * @param null|String $statusMessage The message such as OK, Not Found etc, optional
     *
     * @return $this
     * @throws \RuntimeException
     */
    public function setStatus($statusCode, $statusMessage = null)
    {
        #       Status-Line = HTTP-Version SP Status-Code SP Reason-Phrase CRLF
        if ($statusMessage == null && isset($this->headerStatusCodes[$statusCode])) {
            $statusMessage = $this->headerStatusCodes[$statusCode];
        }
        if (empty($statusMessage)) {
            throw new \RuntimeException("Status message cannot be empty");
        }
        $headerValue = sprintf("HTTP/1.1 %d %s", $statusCode, $statusMessage);
        $this->setHeader($headerValue, true, $statusCode);
        return $this;
    }

    /**
     * Formats header data to make it look clean
     *
     * @param $value
     *
     * @return string
     */
    private function formatHeaderData($value)
    {
        $headerData = explode(':', $value, 2);
        if (count($headerData) == 1) {
            return $headerData[0];
        } else {
            return sprintf('%s: %s', $this->formatHeaderName($headerData[0]), trim($headerData[1]));
        }
    }

    /**
     * Format header name and returns it
     *
     * @param $headerName
     *
     * @return mixed
     */
    private function formatHeaderName($headerName)
    {
        return str_replace(' ', '-', ucwords(strtolower(str_replace(array('-', '_'), ' ', $headerName))));
    }

    /**
     * This renders the content and returns it
     *
     * @return null|string
     */
    private function renderBody()
    {
        $return = null;
        switch (gettype($this->body)) {
            case 'array':
            case 'object':
                $return = json_encode((array)$this->body, JSON_FORCE_OBJECT | JSON_NUMERIC_CHECK);
                break;
            default:
                $return = $this->body;
        }
        return $return;
    }
}
