<?php
/**
 * Created by PhpStorm.
 * User: henrikpejer
 * Date: 2018-09-27
 * Time: 17:23
 */

namespace DHP\components\router;

use DHP\components\module\Module;

class Route extends Module implements RouteInterface
{

    /** @var string uri that this route should react to */
    private $uri;

    /** @var callable The callable that should be triggered when this route is triggered */
    private $callable;

    public function __construct(string $uri, callable $callable)
    {
        $this->uri      = $uri;
        $this->callable = $callable;
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

    /**
     * Check if this route matches the given uri.
     *
     * @param string $uri
     * @return bool
     */
    public function match(string $uri)
    {
        // TODO: Implement match() method.
    }
}
