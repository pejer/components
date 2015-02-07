<?php
/**
 * Created by PhpStorm.
 * User: henrikpejer
 * Date: 07/02/15
 * Time: 16:05
 */

namespace DHP\components\abstractClasses;

// @codeCoverageIgnoreStart
/**
 * Class Middleware
 *
 * Abstract class that all middleware should extend
 *
 * @package DHP\components\abstractClasses
 */
abstract class Middleware
{
    abstract public function __invoke();
}
// @codeCoverageIgnoreEnd