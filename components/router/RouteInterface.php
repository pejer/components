<?php

namespace DHP\kaerna\interfaces;

/**
 * Interface RouteInterface
 *
 * Example of url:
 *  * blog/{id}/{?action} <== so action is _optional_
 *  * blog/{id:num} <== so id will need to be numbers
 *
 * We should also support a format (?): .html etc...
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

    /**
     * Check if this route matches the given uri.
     *
     * @param string $uri
     * @return bool
     */
    public function match(string $uri);
}
