<?php

declare(strict_types=1);

const ENV      = "local";
const WEB_ROOT = __DIR__;

require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'bootstrap.php';

$service = new \DHP\components\service\Service();

$service->addTransient('DHP\components\FluidTransient', 'fluid');
$service->addSingleton('DHP\components\Fluid');

spl_object_hash($service->load('fluid'));
spl_object_hash($service->load('DHP\components\Fluid'));
spl_object_hash($service->load('DHP\components\Fluid'));
spl_object_hash($service->load('fluid'));

var_dump($service->extractScope());
var_dump($service->clone());

var_dump(memory_get_peak_usage(true) / 1024 / 1024 . ' MB');
var_dump(memory_get_usage() / 1024 / 1024 . ' MB');
