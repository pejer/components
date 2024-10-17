<?php

declare(strict_types=1);

namespace DHP\components\service;

enum STATE
{
  case NOT_SET;
  case ERROR;
}

/** @package DHP\components\service */
class Scope
{

  private array $storage = [];
  private array $aliases = [];

  /**
   * @param mixed $object 
   * @param string $name
   * @param array<string> $aliases 
   * @return void 
   */
  public function store(mixed $object, string $name, array $aliases = [])
  {
    $this->storage[$name] = $object;
    foreach ($aliases as $alias) {
      $this->aliases[$alias] = $name;
    }
  }

  /**
   * @param string $alias 
   * @param string $object 
   * @return void 
   */
  public function replace(string $alias, string $object)
  {
    $this->storage[$this->aliases[$alias]] = $object;
  }
  /**
   * @param string $alias 
   * @return mixed 
   */
  public function get(string $alias): mixed
  {
    $key = $this->aliases[$alias] ?? STATE::NOT_SET;
    if ($key == STATE::NOT_SET) {
      return $key;
    }
    return $this->storage[$this->aliases[$alias]] ?? STATE::NOT_SET;
  }
}
