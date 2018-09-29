<?php
/**
 * Created by Henrik Pejer ( mr@henrikpejer.com )
 * Date: 2018-09-22 19:22
 */

namespace DHP_Karna\core\kernel;

interface StorageInterface extends \ArrayAccess
{

    public function __construct(array $defaultValues = [], StorageInterface $permanentStorage = null);

    /**
     * Sets a value for name
     *
     * @param string $name
     * @param $value mixed
     * @return mixed
     */
    public function set(string $name, $value);

    /**
     * Returns the value or the default value if none is set
     * @param string $name name of value to get
     * @param null $default default value, if it is not set
     * @return mixed
     */
    public function get(string $name, $default = null);

    /**
     * Commits (?) the value
     * @return mixed
     */
    public function save();

    /**
     * Clears the storage.
     *
     * If you supply $values, it will clear the values and set storage
     * to $values;
     *
     * @param array|null $values new values
     * @return mixed
     */
    public function clear(array $values = null);
}