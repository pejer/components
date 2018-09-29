<?php
/**
 * Created by Henrik Pejer ( mr@henrikpejer.com )
 * Date: 2018-09-22 21:29
 */

namespace DHP_Karna\core;


use DHP_Karna\core\kernel\StorageInterface;

/**
 * Class Storage
 * @package DHP_Karna\core
 */
class Storage implements StorageInterface
{
    /** @var array */
    private $values;

    /** @var StorageInterface */
    private $permanentStorage;

    public function __construct(array $defaultValues = [], StorageInterface $permanentStorage = null)
    {
        $this->values           = $defaultValues;
        $this->permanentStorage = $permanentStorage;
    }

    public function set(string $name, $value)
    {
        $this->values[$name] = $value;
    }

    public function get(string $name, $default = null)
    {
        $return = $default;
        if (isset($this->values[$name])) {
            $return = $this->values[$name];
        }
        return $return;
    }

    public function save()
    {
        $return = true;
        if (isset($this->permanentStorage)) {
            $this->permanentStorage->clear($this->values);
            $return = $this->permanentStorage->save();
        }
        return $return;
    }

    public function clear(array $values = null)
    {
        $this->values = [];
        if (isset($values)) {
            $this->values = $values;
        }
    }

    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->values[] = $value;
        } else {
            $this->values[$offset] = $value;
        }
    }

    public function offsetExists($offset)
    {
        return isset($this->values[$offset]);
    }

    public function offsetUnset($offset)
    {
        unset($this->values[$offset]);
    }

    public function offsetGet($offset)
    {
        return isset($this->values[$offset]) ? $this->values[$offset] : null;
    }
}
