<?php
/**
 * Created by PhpStorm.
 * User: henrikpejer
 * Date: 2018-09-29
 * Time: 17:36
 */

namespace DHP\kaerna\interfaces;


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
interface ProxyInterface
{
    public function addMethodCall(string $method, ...$arguments);

    public function addConstructorArguments(...$arguments);

    public function init();
}