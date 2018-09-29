<?php
/**
 * Created by Henrik Pejer ( mr@henrikpejer.com )
 * Date: 2018-09-22 19:17
 */

namespace DHP\kaerna\interfaces;

interface MiddlewareInterface extends ServiceInterface
{
    public function __invoke(
        RequestInterface $request,
        ResponseInterface $response,
        ContainerInterface $container,
        callable $next
    ): ResponseInterface;
}