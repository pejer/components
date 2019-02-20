<?php
/**
 * Created by PhpStorm.
 * User: henrikpejer
 * Date: 2018-04-11
 * Time: 08:35
 */

namespace DHP\components\layout;

/**
 * Interface LayoutCompileInterface
 *
 * Lets try to compile something that will be a plain required file. Since many
 * argue that PHP already _is_ a templating language, why use anything else?
 *
 * If we simply generate a plain php-file that a class can include, then we'd have
 * the most basic of templates.
 *
 * @package DHP\kaerna\interface
 */
interface LayoutCompileInterface
{
    public function __construct();

    public function compile($template);
}
