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
   * @param string $alias 
   * @param mixed ...$furtherAliases 
   * @return void 
   */
  public function store(mixed $object, ...$aliases)
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
