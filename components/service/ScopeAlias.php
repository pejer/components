<?php

declare(strict_types=1);

namespace DHP\components\service;

/** @package DHP\components\service */
class ScopeAlias extends Scope
{
  /** @var array<string> $aliases */
  private array $aliases = [];

  /**
   * @param string $name 
   * @param mixed $object 
   * @param array<string> $aliases 
   * @return void 
   */
  public function store(string $name, mixed $object, array $aliases = []) {}

  /**
   * @param string $name 
   * @return mixed 
   */
  public function get(string $name): mixed
  {
    $ret = parent::get($name);
    if ($ret != STATE::NOT_SET) {
      return $ret;
    }
    $key = $this->aliases[$name] ?? STATE::NOT_SET;
    if ($key == STATE::NOT_SET) {
      return $key;
    }
    return $this->storage[$key] ?? STATE::NOT_SET;
  }
}
