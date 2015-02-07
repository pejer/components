<?php
/**
 * Created by PhpStorm.
 * User: henrikpejer
 * Date: 07/02/15
 * Time: 16:05
 */

namespace DHP\components\abstractClasses;

// @codeCoverageIgnoreStart
abstract class Middleware {
    abstract public function __invoke();
}
// @codeCoverageIgnoreEnd