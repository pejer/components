<?php
namespace DHP\components\dependencyinjection;

    /**
     *
     * Created by: Henrik Pejer, mr@henrikpejer.com
     * Date: 2014-02-07 14:58
     *
     */

/**
 * Class DependencyInjection
 * @package DHP\components\dependencyinjection
 *
 * @SuppressWarnings(PHPMD.StaticAccess)
 * A dependency bucket
 */
class DependencyInjection
{

    const STORE_STANDARD_BUCKET = 'standard';
    /** @var \StdClass store of values */
    private $store;

    /**
     * Initiates the DI store
     */
    public function __construct()
    {
        $this->store = new \stdClass();
    }

    /**
     * Sets a value in the bucket
     *
     * @param        $name
     * @param null $value
     * @param string $bucket
     *
     * @return \DHP\components\dependencyinjection\Proxy|mixed
     */
    public function set($name, $value = null, $bucket = self::STORE_STANDARD_BUCKET)
    {
        $name = $this->cleanName($name);
        if ($value === null) {
            $value = $name;
        }
        if (!isset($this->store->{$bucket})) {
            $this->store->{$bucket} = new \stdClass();
        }
        switch (true) {
            case is_string($value) && isset($this->store->{$bucket}->{$value}):
                $this->store->{$bucket}->{$name} = $this->get($value, $bucket);
                break;
            case (is_string($value) && class_exists($value, true)):
                $this->store->{$bucket}->{$name} = new Proxy($value);
                break;
            default:
                $this->store->{$bucket}->{$name} = $value;
                if (is_object($value)) {
                    /** @noinspection PhpParamsInspection */
                    $this->store->{$bucket}->{get_class($value)} = &$this->store->{$bucket}->{$name};
                }
                break;
        }
        return $this->store->{$bucket}->$name;
    }

    /**
     * This method cleans key names, normalizing keys that most likely are classes.
     *
     * @param string $name key name to check and clean
     *
     * @return string
     */
    private function cleanName($name)
    {
        #if (strpos($name, '\\')) {
        #    $name = '\\' . trim($name, '\\ ');
        #}
        if (class_exists($name, true) || class_exists(trim($name, '\\ '), true)) {
            $name = '\\' . trim($name, '\\ ');
        }
        return $name;
    }

    /**
     * Returns the object you want
     *
     * @param        $name
     * @param string $bucket
     *
     * @return null|mixed
     */
    public function get($name, $bucket = self::STORE_STANDARD_BUCKET)
    {
        $name = $this->cleanName($name);
        switch (true) {
            case (isset($this->store->{$bucket}->{$name}) &&
                is_a($this->store->{$bucket}->{$name}, 'DHP\components\dependencyinjection\Proxy')):
                $instValues = $this->store->{$bucket}->{$name}->get();
                $ret = $this->instantiateObject($instValues['class'], $instValues['args'], $bucket);
                foreach ($instValues['methods'] as $methodAndArgs) {
                    call_user_func_array(
                        array(
                            $ret,
                            $methodAndArgs->method
                        ),
                        $methodAndArgs->args
                    );
                }
                $this->store->{$bucket}->{$name} = &$ret;
                break;
            case isset($this->store->{$bucket}->{$name}):
                $ret = $this->store->{$bucket}->{$name};
                break;
            case (is_string($name) && class_exists($name, true)):
                $this->set($name, null, $bucket);
                $ret = $this->get($name, $bucket);
                break;
            default:
                $ret = null;
        }
        return $ret;
    }

    /**
     * Instantiates the object
     *
     * @param        $class
     * @param array $argsForObject
     * @param string $bucket
     *
     * @throws \RuntimeException
     * @return null|object
     */
    private function instantiateObject($class, array $argsForObject = array(), $bucket = self::STORE_STANDARD_BUCKET)
    {
        $constructorArguments = self::classConstructorArguments($class);
        $classReflector = new \ReflectionClass($class);
        if ($classReflector->isInterface() || $classReflector->isAbstract()) {
            throw new \RuntimeException(
                "Trying to instantiate an interface or abstract class directly. Class was \"{$class}\"."
            );
        }
        $args = array();
        $argsNotUsed = array_values($argsForObject);
        try {
            $args += $this->getConstructorArguments($constructorArguments, $argsForObject, $bucket);
            # add the argsNotUsed to the end, right?
            $args += $argsNotUsed;
            $return = count($args) == 0 ? $classReflector->newInstance() : $classReflector->newInstanceArgs($args);
        } catch (\Exception $e) {
            try {
                $return = count($args) == 0 ? $classReflector->newInstance() :
                    $classReflector->newInstanceArgs($args);
            } catch (\Exception $e) {
                $return = null;
            }
        }
        return $return;
    }

    /**
     * Returns constructor arguments. Returns NULL when unable to load/find class
     *
     * @param $class
     *
     * @return NULL|array
     * @throws \Exception
     */
    public static function classConstructorArguments($class)
    {
        $args = array();
        try {
            $refClass = new \ReflectionClass($class);
            $constructor = $refClass->getConstructor();
            if ($constructor) {
                $params = $constructor->getParameters();
                if ($params) {
                    foreach ($params as $param) {
                        $arg =
                            array(
                                'name'     => $param->getName(),
                                'required' => true,
                                'class'    => null
                            );
                        if ($param->getClass()) {
                            $arg['class'] =
                                $param->getClass()->getName();
                        }
                        if ($param->isOptional()) {
                            $arg['required'] = false;
                            $arg['default'] =
                                $param->getDefaultValue();
                        }
                        $args[] = $arg;
                    }
                }
            }
        } catch (\Exception $e) { # exception thrown, return null
            throw $e;
        }
        return $args;
    }

    /**
     * Extracts the constructor arguments from the arguments for the object
     *
     * @param $constructorArguments
     * @param $argsForObject
     * @param $bucket
     *
     * @return array
     */
    private function getConstructorArguments($constructorArguments, $argsForObject, $bucket)
    {
        $args = array();
        $arg = null;
        foreach ($constructorArguments as $key => $constructorArgument) {
            # get a value, if possible...
            switch (true) {
                case (!empty($constructorArgument['class']) &&
                    ($arg = $this->get($constructorArgument['class'], $bucket)) !== null):
                case (!empty($constructorArgument['name']) &&
                    ($arg = $this->get($constructorArgument['name'], $bucket)) !== null):
                    $args[] = $arg;
                    break;
                case isset($argsForObject[$key]):
                    $args[] = $argsForObject[$key];
                    break;
                case isset($argsForObject[$constructorArgument['name']]):
                    $args[] = $argsForObject[$constructorArgument['name']];
                    break;
                case isset($constructorArgument['default']):
                    $args[] = $constructorArgument['default'];
                    break;
                default:
                    $args[] = null;
            }
        }
        return $args;
    }
}
