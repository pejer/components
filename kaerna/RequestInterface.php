<?php
/**
 * Created by Henrik Pejer ( mr@henrikpejer.com )
 * Date: 2018-09-22 18:58
 */

namespace DHP\kaerna;

interface RequestInterface extends ServiceInterface
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
