<?php
/**
 * Created by Henrik Pejer ( mr@henrikpejer.com )
 * Date: 2015-02-07 11:54
 */

namespace DHP\components;

use DHP\components\abstractClasses\Middleware;
use DHP\components\dependencyinjection\DependencyInjection;
use DHP\components\event\Event;
use DHP\components\response\Response;
use DHP\components\request\Request;
use DHP\components\routing\Routing;

/**
 * Class App
 *
 * This class is the basis for the app
 * @package DHP\components
 */
class App
{
    public $stopRunningRoutes;
    /**
     * @var Request
     */
    private $request;
    /**
     * @var Response
     */
    private $response;
    /**
     * @var Event
     */
    private $event;
    /**
     * @var DependencyInjection
     */
    private $dependencyInjection;
    /**
     * @var Routing
     */
    private $routing;

    /**
     * @param Request $request
     * @param Response $response
     * @param Event $event
     * @param DependencyInjection $dependencyInjection
     * @param Routing $routing
     */
    public function __construct(
        Request $request,
        Response $response,
        Event $event,
        DependencyInjection $dependencyInjection,
        Routing $routing
    )
    {
        $this->request = $request;
        $this->response = $response;
        $this->event = $event;
        $this->dependencyInjection = $dependencyInjection;
        $this->routing = $routing;
    }

    /**
     * Loads the config for the app and initialize it
     *
     * @param array $config
     */
    public function loadAppConfig(Array $config)
    {
        $this->loadRoutes($config['routes']);
        foreach ($config['middleware'] as $middleware) {
            $middleware[1] = isset($middleware[1]) ? $middleware[1] : '';
            $this->apply($middleware);
        }
        foreach ($config['controllers'] as $controller) {
            $controller[1] = isset($controller[1]) ? $controller[1] : null;
            $this->routing->makeRoutesForClass($controller[0], $controller[1]);
        }
        return $this;
    }

    /**
     * Loads and setups the routing for this app
     * @param Array $routes
     */
    public function loadRoutes(Array $routes)
    {
        foreach ($routes as $route) {
            $route['alias'] = isset($route['alias']) ? $route['alias'] : null;
            $this->routing->add(
                $route['method'],
                $route['uri'],
                $route['closure'],
                $route['alias']
            );
        }
        return $this;
    }

    /**
     * With the method, we apply modules, middleware or other
     * functionality to the application
     * @param String|Middleware $applyThis the object, function, closure to apply
     */
    public function apply($applyThis)
    {
        if (is_string($applyThis)) {
            $apply = $this->dependencyInjection->get($applyThis);
        } else {
            $apply = $applyThis;
        }
        if (is_a($apply, '\DHP\components\abstractClasses\Middleware')) {
            $apply();
        }
    }

    /**
     * This will start the app
     */
    public function __invoke()
    {
        $routes = $this->routing->match($this->request->method, $this->request->uri);
        $that = $this;
        $nextClosure = function () use ($that) {
            $that->stopRunningRoutes = false;
        };
        foreach ($routes as $route) {
            # default value is true - meaning that unless routes explicitly call next(), the routing will stop
            $this->stopRunningRoutes = true;
            # if it is an array, we assume this is in the form of a string with a class, and a string with the name
            # of the method to call
            if (is_array($route['closure']) &&
                isset($route['closure']['controller']) &&
                isset($route['closure']['method'])
            ) {
                $controller = $this->dependencyInjection->get($route['closure']['controller']);
                $callable = array($controller, $route['closure']['method']);
            } else {
                $callable = $route['closure'];
            }
            $args = $route['route'];
            array_push($args, $nextClosure, $this->dependencyInjection);
            call_user_func_array($callable, $args);
            if ($this->stopRunningRoutes) {
                break;
            }
        }
        $this->response->send();
    }
}
