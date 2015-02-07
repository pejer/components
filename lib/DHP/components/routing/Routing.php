<?php
/**
 * Created by Henrik Pejer ( mr@henrikpejer.com )
 * Date: 2014-02-09 06:58
 */

namespace DHP\components\routing;


/**
 * Class Routing
 *
 * Routing functionality
 *
 * @package DHP\components\routing
 */
class Routing
{
    /** @var array stores the setup routes */
    private $routes = array(
        'GET'    => array(),
        'POST'   => array(),
        'PUT'    => array(),
        'DELETE' => array(),
        'HEADER' => array(),
        'ANY'    => array()
    );

    /** @var array stores custom parameter types */
    private $customParamTypes = array();

    /** @var array stores route aliases */
    private $aliases = array();

    /**
     * Matches a uri with route or routes and returns them
     *
     * @param string $method
     * @param string $uri
     * @return array
     */
    public function match($method, $uri)
    {
        $routesToMatch = $this->routes['ANY'];
        if (isset($this->routes[$method])) {
            $routesToMatch = array_merge($routesToMatch, $this->routes[$method]);
        }

        $matchingRoutes = array();

        foreach ($routesToMatch as $routeUri => $closure) {
            if (false !== ($routeMatchReturn = $this->matchUriToRoute($uri, $routeUri))) {
                $matchingRoutes[] = array(
                    'closure' => $closure,
                    'route'   => $routeMatchReturn
                );
            }
        }

        return $matchingRoutes;
    }

    /**
     * Used to match uri to routes
     *
     * @param $uri
     * @param $routeUri
     *
     * @return array|bool
     */
    private function matchUriToRoute($uri, $routeUri)
    {
        $haveParams = strpos($routeUri, ':');
        if ($haveParams === false &&
            ($routeUri == $uri || preg_match('#^' . str_replace('*', '.*', $routeUri) . '$#', $uri))
        ) {
            return array();
        }

        if ($haveParams !== false) {
            return $this->parseUriForParameters($uri, $routeUri);
        }
        return false;
    }

    /**
     * This will parse a route, looking like this,
     * blog/:title
     *
     * into
     *
     * array('title'=>'value_in_url')
     *
     * @param $uri
     * @param $routeUri
     *
     * @return array|bool
     */
    private function parseUriForParameters($uri, $routeUri)
    {
        # get parts of uri & routeUri, that is, split by /
        $routeUriParts = explode('/', $routeUri);
        $uriParts = explode('/', $uri);
        if (count($uriParts) != count($routeUriParts)) {
            return false;
        }
        $return = array();
        foreach ($routeUriParts as $index => $part) {
            if ($part != $uriParts[$index]) {
                if ($part{0} != ':') { #wrong route after all!
                    return false;
                }
                $realValue = $this->cleanUriPartForParam($uriParts[$index]);
                $return[] = $this->checkParameterType($part, $realValue);
            }
        }
        return $return;
    }

    /**
     * This will url-decode and normalize a part of a uri.
     *
     * - are treated as spaces ' '
     *
     * @param String $param
     *
     * @return string
     */
    private function cleanUriPartForParam($param)
    {
        $param = str_replace('-', ' ', $param);
        $param = urldecode($param);
        return $param;
    }

    /**
     * Here a parameter type is checked against any custom types that might exist.
     * If a custom parameter type exist (:userId used in examples above), here
     * that closure will be called and whatever that closure returns, this method
     * returns.
     *
     * @param String $parameterType
     * @param String $paramValue
     *
     * @return mixed
     */
    private function checkParameterType($parameterType, $paramValue)
    {
        $parameterType = str_replace(':', '', $parameterType);
        $return = $paramValue;
        if (isset($this->customParamTypes[$parameterType])) {
            $return =
                call_user_func_array(
                    $this->customParamTypes[$parameterType],
                    array($paramValue)
                );
        }
        return $return;
    }

    /**
     * Returns the registered routes
     *
     * @return array
     */
    public function get()
    {
        return $this->routes;
    }

    /**
     * Returns a uri for a route with the given param values
     *
     * If we have a route like this: blog/:title and we supply this
     * value, array('title' => 'here-is-the-title') this method returns
     * blog/here-is-the-title
     *
     * @param string $route
     * @param array $values
     * @return string
     */
    public function uriForRoute($route, array $values)
    {
        $route = isset($this->aliases[$route]) ? $this->aliases[$route] : $route;
        $valuesToReplace = array();

        # makes sure we have a : as first character in each key of the array
        foreach ($values as $key => $value) {
            if ($key{0} != ':') {
                $key = ":{$key}";
            }
            $valuesToReplace[$key] = $value;
        }
        return str_replace(array_keys($valuesToReplace), array_values($valuesToReplace), $route);
    }

    /**
     * Add own parameters types
     *
     *
     * @param          $parameter
     * @param callable $closure
     */
    public function addCustomParameter($parameter, callable $closure)
    {
        $this->customParamTypes[$parameter] = $closure;
    }

    /**
     * @param      $controllerClass
     * @param null $uriNamespace
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function makeRoutesForClass($controllerClass, $uriNamespace = null)
    {
        $controller = new \ReflectionClass($controllerClass);
        foreach ($controller->getMethods(\ReflectionMethod::IS_PUBLIC) as $controllerMethod) {
            $controllerMethodName = $controllerMethod->getName();
            $methodDocComments = self::methodDocComments($controllerMethod);
            if (isset($methodDocComments['method']) && isset($methodDocComments['uri'])) {
                $routeCall = array(
                    'controller' => $controllerClass,
                    'method'     => $controllerMethodName
                );
                $method = explode(',', $methodDocComments['method']);
                $uri = $methodDocComments['uri'];
                if (isset($uriNamespace)) {
                    $uri = trim($uriNamespace, '/') . '/' . trim($uri, '/');
                }
                $routeAlias = isset($methodDocComments['routeAlias']) ? $methodDocComments['routeAlias'] : null;
                $this->add($method, $uri, $routeCall, $routeAlias);
            }
        }
    }

    /**
     * @param $reflectionMethod
     * @return array
     */
    private static function methodDocComments($reflectionMethod)
    {
        $comments = array();
        # get the docs
        /** @noinspection PhpUndefinedMethodInspection */
        $comment = $reflectionMethod->getDocComment();
        if (false !== $comment) {
            $lines = explode("\n", $comment);
            foreach ($lines as $line) {
                $line = trim($line, ' *');
                if (preg_match('/^@([a-z]+) (.*)$/i', $line, $matches)) {
                    $comments[$matches[1]] = $matches[2];
                }
            }
        }
        return $comments;
    }

    /**
     * @param String|Array $httpMethod The http method for which this route should be called
     * @param string $uri The uri for this method
     * @param callable|array $closure The callback or array for calling a method on an object
     * @param string|null $alias alias to use for this route
     * @throws \RuntimeException
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function add($httpMethod, $uri, $closure, $alias = null)
    {
        $httpMethod = is_array($httpMethod) ? $httpMethod : array($httpMethod);
        foreach ($httpMethod as $method) {
            if (isset($this->routes[$method][$uri])) {
                throw new \RuntimeException("A route for that method and uri already exists");
            }
            $this->routes[$method][$uri] = $closure;
            if (isset($alias)) {
                $this->aliases[$alias] = $uri;
            }
        }
    }
}
