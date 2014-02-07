<?php
/**
 * Created by Henrik Pejer ( mr@henrikpejer.com )
 * Date: 2014-02-05 22:04
 */
# Very simple autoloader that works for these tests
function autoload($className)
{
    $className = ltrim($className, '\\');
    $fileName  = '';
    $namespace = '';
    if ($lastNsPos = strripos($className, '\\')) {
        $namespace = substr($className, 0, $lastNsPos);
        $className = substr($className, $lastNsPos + 1);
        $fileName  = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
    }
    $fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';
    include $fileName;
}
set_include_path(get_include_path() . PATH_SEPARATOR . __DIR__.'/../lib');
require_once __DIR__.'/../vendor/autoload.php';
spl_autoload_register('autoload');