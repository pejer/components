<?php
/**
 * Created by Henrik Pejer ( mr@henrikpejer.com )
 * Date: 2018-09-22 18:51
 */

namespace DHP_Karna\core\kernel;

/**
 * Class Kernel
 * @package DHP_Karna\core\kernel
 */
class Kernel implements KernelInterface
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

    public function addMiddleware(MiddlewareInterface $middleware): KernelInterface
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
