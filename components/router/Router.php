<?php
/**
 * Created by PhpStorm.
 * User: henrikpejer
 * Date: 2018-09-27
 * Time: 17:23
 */

namespace DHP\kaerna\router;

use DHP\kaerna\interfaces\RouterInterface;
use DHP\kaerna\interfaces\MiddlewareInterface;
use DHP\kaerna\interfaces\RouteInterface;

class Router implements \DHP\kaerna\interfaces\RouterInterface
{

    public function __construct(string $uri, callable $callable)
    {
        parent::__construct($uri, $callable);
    }

    public function __invoke()
    {
        // TODO: Implement __invoke() method.
    }

    public function addMiddleware(MiddlewareInterface $middleware): RouterInterface
    {
        // TODO: Implement addMiddleware() method.
    }

    public function makeUri(array $parameters)
    {
        // TODO: Implement makeUri() method.
    }

    public function match(string $method, string $uri): ?RouteInterface
    {
        // TODO: Implement match() method.
    }
}