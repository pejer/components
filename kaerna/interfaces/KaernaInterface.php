<?php
/**
 * Created by Henrik Pejer ( mr@henrikpejer.com )
 * Date: 2018-09-22 18:52
 */

namespace DHP\kaerna\interfaces;

interface KaernaInterface extends ServiceInterface
{
    public function __construct(ContainerInterface $container, RequestInterface $request, ResponseInterface $response);

    /**
     * This will return an object from the container.
     *
     * @param string $name What to get.
     * @return mixed
     */
    public function __get($name);

    /**
     * Gets an object from the container and calls it, with the arguments.
     *
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public function __call($name, $arguments);

    public function __invoke(): ResponseInterface;

    /**
     * Adds middleware to the application.
     *
     * @param MiddlewareInterface $middleware
     * @return KaernaInterface
     */
    public function addMiddleware(MiddlewareInterface $middleware): KaernaInterface;

    /**
     * Adds route to the application.
     *
     * @param array $method
     * @param string $uri
     * @param string $name
     * @return RouterInterface
     */
    public function addRoute(array $method, string $uri, string $name): RouterInterface;
}
