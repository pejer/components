<?php
/**
 * Created by Henrik Pejer ( mr@henrikpejer.com )
 * Date: 2018-09-22 21:21
 */

namespace DHP\components\container;

interface RegistryInterface
{
    public function addRoots(array $roots): RegistryInterface;

    public function addExtensions(array $extensions): RegistryInterface;

    public function addPathsToExclude(array $path): RegistryInterface;

    public function getRegistry(): array;
}