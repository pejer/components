<?php
/**
 * Created by Henrik Pejer ( mr@henrikpejer.com )
 * Date: 2018-09-22 19:36
 */

namespace DHP_Karna\core\kernel;

const METHOD_GET     = 'GET';
const METHOD_POST    = 'POST';
const METHOD_PUT     = 'PUT';
const METHOD_DELETE  = 'DELETE';
const METHOD_PATCH   = 'PATCH';
const METHOD_OPTIONS = 'OPTIONS';

interface RouterInterface extends ServiceInterface
{
    public function __construct(string $uri, callable $callable);

    public function __invoke();

    public function addMiddleware(MiddlewareInterface $middleware): RouterInterface;

    public function makeUri(array $parameters);

    public function match(string $method, string $uri): ?RouteInterface;
}