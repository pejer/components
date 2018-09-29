<?php
/**
 * Created by PhpStorm.
 * User: henrikpejer
 * Date: 2018-09-29
 * Time: 13:46
 */

namespace DHP\kaerna\container;

use DHP\kaerna\interfaces\ContainerInterface;
use DHP\kaerna\interfaces\ProxyInterface;

/**
 * Class Proxy
 * @package DHP\kaerna\container
 *
 * This _might_ be used to handle when we want to add logic to the creation
 * of an object - maybe call methods on that object before we return it or
 * perhaps get something else.
 *
 * In the long run it _would_ be sexy to generate a class that is compatible
 * with the class you want but would allow for some lazy-loading of the object.
 */
class Proxy implements ProxyInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;
    /**
     * @var string
     */
    private $classToLoad;

    /** @var array all method calls needed to instantiate this _thing_ */
    private $methodCalls = [];

    /** @var null|object */
    private $instance = null;

    public function __construct(
        ContainerInterface $container,
        string $classToLoad,
        ...$constructorArguments
    ) {
        $this->container   = $container;
        $this->classToLoad = $classToLoad;
        if (!empty($constructorArguments)) {
            $this->addMethodCall('__construct', ...$constructorArguments);
        }
    }

    public function addMethodCall(string $method, ...$arguments)
    {
        if (!isset($this->methodCalls[$method])) {
            $this->methodCalls[$method] = [];
        }
        $this->methodCalls[$method][] = $arguments;
        return $this;
    }

    /**
     * Handle when a method is called on the object.
     * @param $name
     * @param mixed ...$arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        $this->instantiate();
        # check if method exists (?)
        if (in_array($name, get_class_methods($name))) {
            if (empty($arguments)) {
                $return = $this->instance->$name();
            } else {
                $return = $this->instance->$name(...$arguments);
            }
        }
        return $return;
    }

    private function instantiate()
    {
        if (!isset($this->instance)) {
            if (!empty($this->methodCalls['__construct'])) {
                $this->instance = $this->container->get($this->classToLoad, ...$this->methodCalls['__construct'][0]);
            } else {
                $this->instance = $this->container->get($this->classToLoad);
            }

            # call methods

            foreach ($this->methodCalls as $method => $args) {
                if ($method != '__construct') {
                    foreach ($args as $methodArg) {
                        $this->instance->$method(...$methodArg);
                    }
                }
            }
        }
    }

    public function init()
    {
        $this->instantiate();
        return $this->instance;
    }
}
