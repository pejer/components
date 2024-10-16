<?php

declare(strict_types=1);

namespace DHP\components\service;

abstract class Proxy
{
  protected $args;
  protected $obj;
  public function __construct(private Service $service, private string $class,  array ...$args)
  {
    $this->args = $args;
  }

  protected function getConstructor(): \ReflectionMethod
  {
    return (new \ReflectionClass($this->class))->getConstructor();
  }
  protected function buildArguments(\ReflectionMethod $constructor): array
  {
    $return = [];
    $counter = -1;
    foreach ($constructor->getParameters() as $param) {
      ++$counter;
      $class = null;
      $type = $param->getType()->getName();
      $argValue = $this->args[$param->name] ?? $this->args[$counter] ?? null;
      if ($param->getType() && $param->getType()->isBuiltin() == false) {
        $class = $type;
      }
      switch (TRUE) {
        case !empty($class):
          $return[] = $this->service->load($class);
          break;
        case !empty($argValue):
          # If the provided arg is a callable
          # AND the type is not callable
          # öthen we evaluate the callable and use the return as argument value
          if (is_callable($argValue) && $type != "callable") {
            $argValue = $argValue();
          }
          $return[] = $argValue;
          break;
        case $param->isDefaultValueAvailable():
          $return[] = $param->getDefaultValue();
          break;
      }
    }
    return $return;
    return [];
  }
  protected function instantiate(): object
  {
    $args = $this->buildArguments($this->getConstructor());
    return new ($this->class)(...$args);
  }
}
