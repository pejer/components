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
/*$container->set(
    function () {
        $request = \DHP\kaerna\request\Request::createFromEnvironment();
        return $request;
    },
    'DHP\kaerna\request\Request'
);*/

#$kernel = $container->get('DHP\kaerna\interfaces\KaernaInterface');
#$proxy = new \DHP\kaerna\container\Proxy($container, 'DHP\kaerna\request\Request','GET','/slam');
$proxy = $container->set('DHP\kaerna\request\Request', 'request');
$proxy->addConstructorArguments('GET', '/slam');
#var_dump($proxy);

$req = $container->get('request');

var_dump($req);


# $req   = $proxy->init();

var_dump($req->getUri());
