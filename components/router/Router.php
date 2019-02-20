<?php
/**
 * Created by PhpStorm.
 * User: henrikpejer
 * Date: 2018-09-27
 * Time: 17:23
 */

namespace DHP\components\router;

class Router implements RouterInterface
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

    public function match(string $method, string $uri, string $format = ''): ?RouteInterface
    {
        // TODO: Implement match() method.
    }
}
