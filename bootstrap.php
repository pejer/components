<?php

require_once 'autoload.php';

// load environment settings

$ENV_CONFIG = \Symfony\Component\Yaml\Yaml::parseFile(CONFIG_DIR . 'env.' . ENV . '.yml');

$reg = new \DHP\kaerna\container\Registry();
$reg->addExtensions(['php'])
    ->addRoots(
        [
            __DIR__ . \DIRECTORY_SEPARATOR . 'kaerna',
            __DIR__ . \DIRECTORY_SEPARATOR . 'modules'
        ]
    );
$registry = $reg->getRegistry();

/** @var \DHP\kaerna\interfaces\ContainerInterface $container */
$container = new $ENV_CONFIG['services']['container']['class']();

$container->setRegistry($registry);

foreach ($ENV_CONFIG['services'] as $alias => $service) {
    $save       = $service['class'];
    $interfaces = class_implements($save);
    if (!empty($service['factory'])) {
        $method = key($service['factory']);
        $args   = current($service['factory']);
        $save   = function () use ($method, $args) {
            return empty($args) ? ($method)() : ($method)(...$args);
        };
        $alias  = [$alias];
        if (isset($service['alias'])) {
            $alias = array_merge_recursive($alias, $service['alias']);
        }
    }
    if (!is_array($alias)) {
        $alias = [$alias];
    }
    $alias[] = $service['class'];
    $alias   = array_merge_recursive($alias, $interfaces);

    /** @var \DHP\kaerna\interfaces\ProxyInterface $proxy */
    $proxy = $container->set($save, $alias);
    if (!empty($service['params'])) {
        $proxy->addConstructorArguments(...$service['params']);
    }
    if (isset($service['call'])) {
        foreach ($service['call'] as $method => $params) {
            $proxy->addMethodCall($method, ...$params);
        }
    }
}

if ($container->has('memcache')) {
    $memcache = $container->get('memcache');
}
// check some things


/** @var \DHP\kaerna\interfaces\KaernaInterface $kaerna */
$kaerna = $container->get('DHP\kaerna\Kaerna');
$orig   = spl_object_hash($container->get('\request'));

$checks = [
    '\request',
    'request',
    'DHP\kaerna\request\Request',
    '\DHP\kaerna\request\Request',
];
foreach ($checks as $check) {
    $hash = spl_object_hash($container->get($check));
    if ($hash != $orig) {
        var_dump($check . ': This didnt work');
    }
}
#var_dump(($container->get('request')));
/** @var \DHP\kaerna\Event $event */
$event = $container->get('\DHP\kaerna\interfaces\EventInterface');
$event->register(
    'hej.pa.dig',
    function (&$one, &$two) {
        $one .= ' ny';
        echo "hej.pa.dig";
    }
);
$event->register(
    'hej.pa',
    function (&$one, &$two) {
        $one .= ' lattjo';
        echo "hej.pa $one";
    }
);
$one = 'ett';
$two = 'två';
$event->trigger('hej.*.dig', $one, $two);

var_dump($one, $two);

#$proxy = $container->set('DHP\kaerna\request\Request', 'request');
#$proxy->addConstructorArguments('GET', '/slam');
#var_dump($proxy);

#$req = $container->get('request');

#var_dump($req);

#var_dump($req->getUri());
