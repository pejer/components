<?php

namespace DHP_Karna\core\kernel;

/**
 * Interface RouteInterface
 *
 * Example of url:
 *  * blog/{id}/{?action}
 *
 * @package DHP_core\component\interfaces
 */
interface RouteInterface
{
    public function __construct(string $uri, callable $callable);

    /**
     * Should return a working uri for this route.
     *
     * @param array $parameters
     * @return mixed
     */
    public function makeUri(array $parameters);

    /**
     * Adds middleware for this route.
     *
     * @param MiddlewareInterface $middleware The middleware to add for this route.
     * @return RouteInterface
     */
    public function addMiddleware(MiddlewareInterface $middleware): RouteInterface;

    /**
     * Will start the route and trigger whatever it is that it needs.
     * @return mixed
     */
    public function __invoke();
}
