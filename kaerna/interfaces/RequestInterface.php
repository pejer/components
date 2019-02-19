<?php
/**
 * Created by PhpStorm.
 * User: henrikpejer
 * Date: 2019-02-17
 * Time: 12:32
 */

namespace DHP\kaerna\interfaces;

interface RequestInterface
{
    /**
     * @return string
     */
    public function getMethod(): string;

    /**
     * @return null
     */
    public function getUri();

    /**
     * @return null
     */
    public function getBody();

    public function setBody($body);

    /**
     * @return array
     */
    public function getPost(): array;

    /**
     * @return array
     */
    public function getGet(): array;

    /**
     * @return array
     */
    public function getFiles(): array;

    /**
     * @return array
     */
    public function getHeaders(): array;
}