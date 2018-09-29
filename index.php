<?php

require_once 'autoload.php';

$reg = new \DHP_Karna\core\container\Registry();

$reg->addExtensions(['php'])
    ->addRoots(
        [
            __DIR__ . \DIRECTORY_SEPARATOR . 'core',
            __DIR__ . \DIRECTORY_SEPARATOR . 'modules'
        ]
    );

$registry = $reg->getRegistry();

$container = new \DHP_Karna\core\container\Unicorn([], $registry);
$container->set(
    function () {
        $request = \DHP_Karna\core\request\Request::createFromEnvironment();
        return $request;
    },
    'DHP_Karna\core\request\Request'
);

$kernel = $container->get('DHP_Karna\core\kernel\KernelInterface');

var_dump($kernel());