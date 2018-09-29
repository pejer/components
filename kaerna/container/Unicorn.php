<?php
/**
 * Created by Henrik Pejer ( mr@henrikpejer.com )
 * Date: 2018-09-22 21:26
 */

namespace DHP\kaerna\container;

use DHP\kaerna\ContainerInterface;
use http\Exception\RuntimeException;

const NOT_FOUND         = "f4fc80a9a3e948c88a5e7d960305ab7c";
const SERVICE_INTERFACE = "DHP\\kaerna\\ServiceInterface";

class Unicorn implements ContainerInterface
{
    /**
     * @var array|mixed Where to store the data.
     */
    public $storage;
    public $registry;

    /**
     * Unicorn constructor.
     * @param mixed $storage Where/how we should store this.
     * @param array $registry Registry of interfaces and the class that uses it.
     */
    public function __construct($storage = [], array $registry = [])
    {
        $this->storage  = $storage;
        $this->registry = $registry;

        #$this->storage[get_class($this)] = &$this;
        $this->set($this);
    }

    public function get($name, ...$objectArgs)
    {
        $object = $this->load($this->sanitizeName($name), ...$objectArgs);
        if ($object === NOT_FOUND) {
            throw new \RuntimeException("$name was not found");
        }
        $this->set($object);
        return $object;
    }

    /**
     * Loads the object and returns it.
     *
     * If an object that is being instantiated, implements the interface DHP_core\component\interfaces\Service
     * it will be instantiated only once and further calls to load will return that same object.
     *
     * @param string $name The name of what we should load.
     * @return mixed Will return DHP_core\component\container::NOT_FOUND if something is not found... duuuh.
     * @throws \ReflectionException
     */
    private function load(string $name, ...$objectArgs)
    {
        $object = NOT_FOUND;
        // Is it an interface....?
        if (null != ($classfrominterface = $this->getClassForInterface($name))) {
            $name = $classfrominterface;
        }
        switch (true) {
            case $this->has($name):
                $object = $this->storage[$name];
                if (is_callable($object)) {
                    $object = $object();
                    unset($this->storage[$name]);
                    $this->set($object);
                }
                break;
            case class_exists($name):
                // Todo: handle how to do when we provide _some_ constructor agruments...
                $constructorArgs = [];
                foreach ($this->getConstructorArguments($name) as $key => $arg) {
                    if ($arg === null) {
                        $constructorArgs[] = array_shift($objectArgs);
                    } else {
                        $constructorArgs[] = $this->get($arg);
                    }
                }
                $object = new $name(...$constructorArgs);
                if (in_array(SERVICE_INTERFACE, class_implements($object))) {
                    $this->storage[$name] = $object;
                }
                break;
        }
        return $object;
    }

    public function has($name)
    {
        $return = false;
        if (is_string($name) && isset($this->storage[$name])) {
            $return = true;
        }
        return $return;
    }

    public function set($object, $alias = null, $overwrite = false)
    {
        if (empty($alias)) {
            switch (true) {
                case is_string($object):
                    $alias = $object;
                    break;
                case is_object($object):
                    $alias = get_class($object);
                    break;
            }
        }
        $alias = $this->sanitizeName($alias);
        if ($this->has($alias)) {
            if ($overwrite) {
                $this->storage[$alias] = $object;
            }
            return $this->storage[$alias];
        }
        $objectToSave = $object;
        $aliases      = [];
        if (is_string($object) && class_exists($object)) {
            $objectToSave     = $this->load($object);
            $aliases[$object] = true;
        }


        if (is_object($object)) {
            $interfaces = class_implements($object);
            // Only add interfaces that belong to objects that are services..
            if (in_array(SERVICE_INTERFACE, class_implements($object))) {
                $aliases[get_class($objectToSave)] = true;
                // also add for interfaces it implements?
                foreach ($interfaces as $interface) {
                    if ($interface != SERVICE_INTERFACE) {
                        $aliases[$interface] = true;
                    }
                }
            }
        }
        $this->storage[$alias] = $objectToSave;
        foreach (array_keys($aliases) as $extraAlias) {
            if ($extraAlias != $alias) {
                $this->storage[$extraAlias] = &$this->storage[$alias];
            }
        }
        return $objectToSave;
    }

    private function sanitizeName(string $name)
    {
        return '\\' . trim($name, '\\ ');
    }

    /**
     * This will get the array of constructor arguments you should instantiate.
     *
     * @param string $class The class to get the constructor arguments for.
     * @return array The constructor arguments for the object to instantiate.
     */
    private function getConstructorArguments(string $class)
    {
        $return = [];
        try {
            $constructor = new \ReflectionMethod($class, '__construct');
            foreach ($constructor->getParameters() as $arg) {
                // TODO: What to do when there are no hints.
                // TODO: What to do when the hints is for a class.
                // TODO: Can we make this into proxies?
                $argClass = $arg->getClass();
                if (!empty($argClass)) {
                    $argClass = $argClass->name;
                    $ref      = new \ReflectionClass($argClass);
                    if ($ref->isInterface()) {
                        $argClass = $this->getClassForInterface($ref->name);
                    }
                    $return[] = $argClass;
                } else {
                    $return[] = null;
                }
            }
        } catch (\Exception $e) {
        }
        return $return;
    }

    private function getClassForInterface(string $interface)
    {
        $return = null;
        $interface = $this->sanitizeName($interface);
        if (isset($this->registry[$interface])) {
            // TODO: What to do when there are more classes for the same interface?
            $return = $this->registry[$interface][0];
        }
        return $return;
    }
}
