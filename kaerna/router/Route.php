<?php
/**
 * Created by PhpStorm.
 * User: henrikpejer
 * Date: 2018-09-27
 * Time: 17:23
 */

namespace DHP\kaerna\interfaces\router;

use DHP\kaerna\MiddlewareInterface;
use DHP\kaerna\RouteInterface;

class Route implements RouteInterface
{

    public function __construct(string $uri, callable $callable)
    {
        parent::__construct($uri, $callable);
    }

    /**
     * Should return a working uri for this route.
     *
     * @param array $parameters
     * @return mixed
     */
    public function makeUri(array $parameters)
    {
        // TODO: Implement makeUri() method.
    }

    /**
     * Adds middleware for this route.
     *
     * @param MiddlewareInterface $middleware The middleware to add for this route.
     * @return RouteInterface
     */
    public function addMiddleware(MiddlewareInterface $middleware): RouteInterface
    {
        // TODO: Implement addMiddleware() method.
    }

    /**
     * Will start the route and trigger whatever it is that it needs.
     * @return mixed
     */
    public function __invoke()
    {
        // TODO: Implement __invoke() method.
    }
}