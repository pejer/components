<?php
namespace DHP\components\utils;

    /**
     *
     * Created by: Henrik Pejer, mr@henrikpejer.com
     * Date: 2014-02-13 14:49
     *
     */

/**
 * Sets constant properties on an object and does not allow them to change, like a constant.
 *
 * You can also define different environments for the values, think bucket, meaning that
 * you could use this a config-file for different environrments, say dev and production.
 *
 * Class Constants
 *
 * @package DHP\components\utils
 */
class Constants
{

    const DEFAULT_ENVIRONMENT = 'GLOBAL';
    protected $values = array();
    protected $currentEnvironment = self::DEFAULT_ENVIRONMENT;

    /**
     * Initiates the constants object. You can provide the initial values
     * along with a current environment
     *
     * @param array  $initialValues      initial values, with keys as their names
     * @param string $defaultEnvironment name of default environment, if none is provided
     */
    public function __construct(array $initialValues = array(), $defaultEnvironment = null)
    {
        if (isset( $defaultEnvironment )) {
            $this->currentEnvironment = $defaultEnvironment;
        }
        $this->values[$this->currentEnvironment] = $initialValues;
    }

    /**
     * Sets a new default environment
     *
     * @param string $environment name of new environment
     *
     * @return $this
     */
    public function setDefaultEnvironment($environment)
    {
        if (!isset( $this->values[$environment] )) {
            $this->values[$environment] = array();
        }
        $this->currentEnvironment = $environment;
        return $this;
    }

    /**
     * Magic get method - returns the variable asked for, or null if it does not exist
     * in the current environment or the default one.
     *
     * @param string $name the name of the variable you are looking for
     *
     * @return mixed|null
     */
    public function __get($name)
    {
        switch (true) {
            case isset( $this->values[$this->currentEnvironment][$name] ):
                return $this->values[$this->currentEnvironment][$name];
                break;
            case isset( $this->values[self::DEFAULT_ENVIRONMENT][$name] ):
                return $this->values[self::DEFAULT_ENVIRONMENT][$name];
                break;
        }
        return null;
    }

    /**
     * Stores the value of the
     *
     * @param string $name  name of variable
     * @param mixed  $value the value to be stored
     *
     * @throws \RuntimeException
     */
    public function __set($name, $value)
    {
        if (isset( $this->values[$this->currentEnvironment][$name] )) {
            throw new \RuntimeException("Can not update value of existing constant");
        }
        $this->values[$this->currentEnvironment][$name] = $value;
    }

    /**
     * Used to set or get a variable with environment.
     *
     * @param $name
     * @param $arguments
     *
     * @return $this|null
     * @throws \RuntimeException
     */
    public function __call($name, $arguments)
    {
        list( $environment, $value ) = $arguments;
        if (isset( $this->values[$environment][$name] )) {
            throw new \RuntimeException("Can not update value of existing constant");
        }
        $this->values[$environment][$name] = $value;
        return $this;
    }
}
