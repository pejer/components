<?php
/**
 * Created by Henrik Pejer ( mr@henrikpejer.com )
 * Date: 2018-09-22 18:59
 */

namespace DHP\kaerna\interfaces;


interface ResponseInterface
{
    public function send();

    public function appendBody(string $body);

    public function getBody();

    public function setBody($body);

    public function getHeaders();

    public function setHeader(string $name, string $value, int $httpStatus = null);

    public function setStatus(int $statusNumber, string $statusMessage = null);

    /**
     * Sets an attribute on the response object.
     *
     * @param string $name  the name of the attribute
     * @param mixed  $value the value of the attribute
     * @return self a clone of self
     */
    public function setAttribute(string $name, $value);

    /**
     * Sets several attributes on the response object.
     *
     * @param array $attributes A key => value array where key will be the name of the attribute.
     * @return self A clone of self
     */
    public function setAttributes(array $attributes);

    /**
     * Returns a single attribute. If not set, returns default.
     *
     * @param string $name The attribute to return;
     * @param null   $default
     * @return mixed
     */
    public function getAttribute(string $name, $default = null);

    /**
     * Returns an array with keys for names. Default will be used when no value is found.
     *
     * @param array $names
     * @param null  $default
     * @return array
     */
    public function getAttributes(array $names, $default = null): array;

}