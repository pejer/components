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

  /**
   * @param mixed $object 
   * @param string $name
   * @return void 
   */
  public function store(string $name, mixed $object)
  {
    $this->storage[$name] = $object;
  }

  /**
   * @param string $name 
   * @param string $object 
   * @return void 
   */
  public function replace(string $name, string $object)
  {
    $this->storage[$name] = $object;
  }
  /**
   * @param string $name 
   * @return mixed 
   */
  public function get(string $name): mixed
  {
    return $this->storage[$name] ?? STATE::NOT_SET;
  }
}
