<?php
namespace DHP\components\utils;

/**
 *
 * Created by: Henrik Pejer, mr@henrikpejer.com
 * Date: 2014-02-13 16:05
 *
 */
class Variables extends Constants
{

    /**
     * Magic set-method.
     *
     * @param string $name
     * @param mixed $value
     *
     * @return $this
     */
    public function __set($name, $value)
    {
        $this->values[$this->currentEnvironment][$name] = $value;
    }

    /**
     * Magic call method - set value based on arguments provided
     *
     * @param $name
     * @param $arguments
     *
     * @return $this|null
     */
    public function __call($name, $arguments)
    {
        list($environment, $value) = $arguments;
        $this->values[$environment][$name] = $value;
        return $this;
    }
}
