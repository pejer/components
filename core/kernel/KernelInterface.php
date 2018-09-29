<?php
/**
 * Created by Henrik Pejer ( mr@henrikpejer.com )
 * Date: 2018-09-22 18:52
 */

namespace DHP_Karna\core\kernel;

interface KernelInterface extends ServiceInterface
{
    public function __construct(ContainerInterface $container, RequestInterface $request, ResponseInterface $response);

    public function __get($name);

    public function __call($name, $arguments);

    public function __invoke(): ResponseInterface;

    public function addMiddleware(MiddlewareInterface $middleware): KernelInterface;

    public function addRoute(array $method, string $uri, string $name): RouterInterface;
}