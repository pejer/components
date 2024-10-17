<?php

declare(strict_types=1);

namespace DHP\components\service;

use ReflectionMethod;
use ReflectionException;

/** @package DHP\components\service */
abstract class Proxy
{
  /** @var mixed[] */
  protected array $args = [];
  protected mixed $obj;
  /**
   * @param Service $service 
   * @param class-string $class 
   * @param mixed[] $args 
   * @return void 
   */
  public function __construct(private Service $service, private string $class, ?array $args = [])
  {
    $this->args = empty($args) ? [] : $args;
  }

  protected function getConstructor(): ?\ReflectionMethod
  {
    return (new \ReflectionClass($this->class))->getConstructor();
  }
  /**
   * @param ReflectionMethod $constructor 
   * @return array 
   * @throws ReflectionException 
   */
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
          # then we evaluate the callable and use the return as argument value
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
  }
  protected function instantiate(): object
  {
    $constructor = $this->getConstructor();
    $args = $constructor != null ? $this->buildArguments($constructor) : [];
    return new ($this->class)(...$args);
  }
}
