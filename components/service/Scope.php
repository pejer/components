<?php

declare(strict_types=1);

namespace DHP\components\service;

enum STATE
{
  case NOT_SET;
  case ERROR;
}

class Scope
{

  private array $storage = [];
  private array $aliases = [];

  /**
   * @param mixed $object 
   * @param array $alias 
   * @return void 
   */
  public function store(mixed $object, array $aliases = [])
  {
    $this->storage[] = $object;
    end($this->storage);
    $key = key($this->storage);
    foreach ($aliases as $alias) {
      $this->aliases[$alias] = $key;
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
