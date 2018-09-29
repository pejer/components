<?php

require_once 'autoload.php';

$reg = new \DHP\kaerna\container\Registry();

$reg->addExtensions(['php'])
    ->addRoots(
        [
            __DIR__ . \DIRECTORY_SEPARATOR . 'kaerna',
            __DIR__ . \DIRECTORY_SEPARATOR . 'modules'
        ]
    );

$registry = $reg->getRegistry();

$container = new \DHP\kaerna\container\Unicorn([], $registry);
$container->set(
    function () {
        $request = \DHP\kaerna\request\Request::createFromEnvironment();
        return $request;
    },
    'DHP\kaerna\request\Request'
);

$kernel = $container->get('DHP\kaerna\interfaces\KaernaInterface');

var_dump($kernel());