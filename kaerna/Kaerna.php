<?php
/**
 * Created by Henrik Pejer ( mr@henrikpejer.com )
 * Date: 2018-09-22 18:51
 */

namespace DHP\kaerna;

use DHP\kaerna\interfaces\ContainerInterface;
use DHP\kaerna\interfaces\KaernaInterface;
use DHP\kaerna\interfaces\RequestInterface;
use DHP\kaerna\interfaces\ResponseInterface;
use DHP\kaerna\interfaces\MiddlewareInterface;
use DHP\kaerna\interfaces\RouterInterface;

/**
 * Class Kernel
 * @package DHP\core\kernel
 */
class Kaerna implements KaernaInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;
    /**
     * @var RequestInterface
     */
    private $request;
    /**
     * @var ResponseInterface
     */
    private $response;
    /** @var null | string */
    private $currentMiddleware;

    public function __construct(ContainerInterface $container, RequestInterface $request, ResponseInterface $response)
    {
        $this->container = $container;
        $this->request   = $request;
        $this->response  = $response;
    }

    public function __get($name)
    {
        // TODO: Implement __get() method.
    }

    public function __call($name, $arguments)
    {
        // TODO: Implement __call() method.
    }

    public function __invoke(): ResponseInterface
    {
        $response = $this->response;
        if (isset($this->currentMiddleware)) {
            $response = ($this->currentMiddleware)($this->request, $this->response);
        }
        return $response;
    }

    public function addMiddleware(MiddlewareInterface $middleware): KaernaInterface
    {

        if (is_string($middleware)) {
            $middleware = $this->container->get($middleware);
        }

        if (!isset($this->currentMiddleware)) {
            $this->currentMiddleware = $this;
        }

        $next      = $this->currentMiddleware;
        $container = $this->container;

        $this->currentMiddleware = function (...$params) use ($middleware, $container, $next) {
            $params[] = $container;
            $params[] = $next;
            return $middleware(...$params);
        };

        return $this;
    }

    public function addRoute(array $method, string $uri, string $name): RouterInterface
    {
        // TODO: Implement addRoute() method.
    }
}
