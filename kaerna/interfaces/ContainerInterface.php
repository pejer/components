<?php
/**
 * Created by PhpStorm.
 * User: henrikpejer
 * Date: 2019-02-17
 * Time: 12:33
 */

namespace DHP\kaerna\interfaces;

interface ContainerInterface
{
    public function setRegistry(array $registry);

    public function set($object, $aliases = null, $overwrite = false, ...$constructorArguments);

    public function has($name);

    public function get($name, ...$objectArgs);

    public function resolve($name);
}