<?php
/**
 * Default, this just includes the composer autoloader.
 */

// Lets define some stuff we rely on
if (!defined('STDIN')) {
    define('STDIN', fopen("php://stdin", "r"));
}

return require_once __DIR__ . sprintf('%1$svendor%1$sautoload.php', DIRECTORY_SEPARATOR);
