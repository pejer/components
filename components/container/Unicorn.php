<?php
/**
 * Created by Henrik Pejer ( mr@henrikpejer.com )
 * Date: 2018-09-22 21:26
 */

namespace DHP\components\container;

const NOT_FOUND         = "f4fc80a9a3e948c88a5e7d960305ab7c";
const SERVICE_INTERFACE = "DHP\\components\\container\\ServiceInterface";
const PROXY_INTERFACE   = "DHP\\components\\container\\ProxyInterface";

class Unicorn implements ContainerInterface
{
    /** /@var array|mixed Where to store the data. */
    public $storage = [];
    /** @var array */
    public $registry = [];
    /** @var array */
    public $aliases = [];

    /**
     * Unicorn constructor.
     * @param mixed $storage Where/how we should store this.
     * @param array $registry Registry of interfaces and the class that uses it.
     */
    public function __construct($storage = [], array $registry = [])
    {
        $this->storage = $storage;
        $this->setRegistry($registry);

        #$this->storage[get_class($this)] = &$this;
        $this->set($this);
    }

    public function setRegistry(array $registry)
    {
        $this->registry = $registry;
        return true;
    }

    public function set($object, $aliases = null, $overwrite = false, ...$constructorArguments)
    {
        $alias = $aliases;
        if (isset($aliases) && !\is_array($aliases)) {
            $aliases = is_array($aliases) ? $aliases : [$aliases];
        }
        if (!empty($aliases)) {
            $alias   = \array_shift($aliases);
            $aliases = \array_flip($aliases);
        }
        if (empty($alias)) {
            switch (true) {
                case is_string($object):
                    $alias = $object;
                    break;
                case is_object($object):
                    $alias = get_class($object);
                    break;
            }
        } else {
            if (\is_object($object)) {
                $aliases[\get_class($object)] = 1;
            }
        }

        $alias = $this->sanitizeName($alias);
        if (($alias_check = $this->has($alias)) !== false) {
            if ($overwrite) {
                $this->storage[$alias_check] = $object;
            }
            return $this->storage[$alias_check];
        }

        $objectToSave = $object;
        if (!isset($aliases)) {
            $aliases = [];
        }

        if (is_string($object) && class_exists($object)) {
            if (empty($constructorArguments)) {
                $objectToSave         = new Proxy($this, $object);
                $constructorArguments = $this->getConstructorArguments($object);

                $args = [];
                foreach ($constructorArguments as $arg) {
                    if ($arg == null) {
                        continue;
                    }
                    $args[] = $this->get($arg);
                }
                $objectToSave->addConstructorArguments(...$args);
            } else {
                $objectToSave = new Proxy($this, $object, ...$constructorArguments);
            }
            $aliases[$object] = true;
            $aliases[$alias]  = true;
        }


        $interfaces = class_implements($object);
        // Only add interfaces that belong to objects that are services..
        if (in_array(SERVICE_INTERFACE, class_implements($object))) {
            if (!in_array(PROXY_INTERFACE, class_implements($objectToSave))) {
                $aliases[get_class($objectToSave)] = true;
            }
            // also add for interfaces it implements?
            foreach ($interfaces as $interface) {
                if ($interface != SERVICE_INTERFACE) {
                    $aliases[$interface] = true;
                }
            }
        }

        $this->storage[$alias] = $objectToSave;
        $this->aliases[$alias] = $alias;
        if (is_string($object)) {
            $this->aliases[$object] = $alias;
        }

        foreach (array_keys($aliases) as $extraAlias) {
            if ($extraAlias != $alias) {
                $this->aliases[$this->sanitizeName($extraAlias)] = $alias;
            }
        }

        return $objectToSave;
    }

    private function sanitizeName(string $name)
    {
        return '\\' . trim($name, "\\ ");
    }

    public function has($name)
    {
        $return = false;
        if (is_string($name)) {
            $sanitize_name = $this->sanitizeName($name);
            if (isset($this->aliases[$sanitize_name])) {
                $return = $this->aliases[$sanitize_name];
            }
        }
        return $return;
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
                        $argClass = $this->getClassForInterface($this->sanitizeName($ref->name));
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
        if (isset($this->registry[$interface])) {
            // TODO: What to do when there are more classes for the same interface?
            $return = $this->registry[$interface][0];
        }
        return $return;
    }

    public function get($name, ...$objectArgs)
    {
        $name   = $this->sanitizeName($name);
        $object = $this->load($name, ...$objectArgs);
        if ($object === NOT_FOUND) {
            throw new \RuntimeException("$name was not found");
        }
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
     */
    private function load(string $name, ...$objectArgs)
    {
        $object = NOT_FOUND;
        // Is it an interface....?
        if (null != ($classFromInterface = $this->getClassForInterface($name))) {
            $name = $classFromInterface;
        }
        switch (true) {
            case $this->has($name) !== false:
                $name   = $this->aliases[$name];
                $object = $this->storage[$name];
                if (is_callable($object)) {
                    $object = $object();
                    $this->set($object, $name, true);
                }
                if (in_array(PROXY_INTERFACE, \class_implements($object))) {
                    /** @var ProxyInterface $object */
                    if (!empty($objectArgs)) {
                        $object->addConstructorArguments(...$objectArgs);
                    }
                    $object = $object->init();
                    $this->set($object, $name, true);
                }
                break;
            case class_exists($name):
                // Todo: handle how to do when we provide _some_ constructor agruments...
                $constructorArgs = [];
                foreach ($this->getConstructorArguments($name) as $key => $arg) {
                    if ($arg === null) {
                        if (!empty($objectArgs)) {
                            $constructorArgs[] = array_shift($objectArgs);
                        }
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

    public function resolve($name)
    {
        $return = null;
        $name   = $this->sanitizeName($name);
        if (($alias = $this->has($name)) !== false) {
            if (in_array(PROXY_INTERFACE, class_implements($this->storage[$alias]))) {
                $return = $this->storage[$alias]->classToLoad;
            }
        }

        return $return;
    }
}
