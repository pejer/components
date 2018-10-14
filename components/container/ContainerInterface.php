<?php
/**
 * Created by Henrik Pejer ( mr@henrikpejer.com )
 * Date: 2018-09-22 19:06
 */

namespace DHP\components\container;

interface ContainerInterface
{
    /**
     * Returns the thing if it exists in the container.
     *
     * @param string $name What we want to get.
     * @return null | mixed
     */
    public function get($name, ...$objectArgs);

    /**
     * Check if we have a certain thing in the container.
     *
     * @param string $name The name of the thing we want.
     * @return false|string false when this isn't set, the name in storage for this if it exists.
     */
    public function has($name);

    /**
     * Stores things in the container.
     *
     * If it is a string and it is a class that we can load, we wil proxy it. Making it so that we can lazy-load
     * it when we need it.
     *
     * @param mixed $object The thing to store in the container.
     * @param string|array $alias The name you want to give this object.
     * @param bool $overwrite If we should overwrite the existing value, if it exists.
     * @return mixed
     */
    public function set($object, $alias, $overwrite = false);

    public function resolve($name);

    public function setRegistry(array $registry);
}