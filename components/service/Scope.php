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
  /** @var array<string> $aliases */
  private array $aliases = [];

  protected array $storage = [];
  /**
   * @param string $name 
   * @param mixed $object 
   * @param array<string> $aliases 
   * @return void 
   */
  public function store(mixed $object, array $aliases = []) {
    $this->storage[] = $object;
    end($this->storage);
    $key = key($this->storage);
    // aliases - lets allow aliases to have arrays of things for an alias
    // TODO: figure out how to handle aliases where there are several potential matches
    foreach($aliases as $alias){
      if (!isset($this->aliases[$alias])){
        $this->aliases[$alias] = [];
      }
      $this->aliases[$alias][] = $key;
    }
  }

  /**
   * @param string $name 
   * @return mixed 
   */
  public function get(string $name): mixed
  {
    $key = $this->aliases[$name] ?? STATE::NOT_SET;
    if ($key == STATE::NOT_SET) {
      return $key;
    }
    $key = $key[0];
    return $this->storage[$key] ?? STATE::NOT_SET;
  }
}
