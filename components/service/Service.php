<?php

declare(strict_types=1);

namespace DHP\components\service;

class Service
{
  private Scope $scope_storage;

  public function __construct(?Scope $existingScope = null)
  {
    $this->scope_storage = match (true) {
      empty($existingScope) => new Scope(),
      default => $existingScope
    };
  }

  public function clone()
  {
    return clone $this;
  }

  // TODO: Handle args provided
  public function load(string $class)
  {
    $obj = $this->scope_storage->get($class);
    if ($obj == STATE::NOT_SET && \class_exists($class)) {
      $this->addSingleton($class);
      $obj = $this->scope_storage->get($class);
    }
    return $obj();
  }

  public function addSingleton(mixed $object, ?string $alias = null, array ...$args)
  {
    $this->add('singleton', $object, $alias, $args);
  }
  public function addTransient(mixed $object, ?string $alias = null, array ...$args)
  {
    $this->add('transient', $object, $alias, $args);
  }

  private function add(string $type, mixed $object, ?string $alias = null, array ...$args)
  {
    $proxy = match ($type) {
      "singleton" => new Singleton($this, $object, $args),
      "transient" => new Transient($this, $object, $args)
    };
    $aliases = [];
    if (!empty($alias)) {
      $aliases[] = $alias;
    }
    foreach ($this->getAliases($object) as $alias) {
      $aliases[] = $alias;
    }
    $this->scope_storage->store($proxy, ...$aliases);
  }
  private function getAliases(string|object $id)
  {

    if (is_string($id) && !class_exists($id)) {
      yield from [$id];
    }
    $class = match (true) {
      is_object($id) => $id::class,
      default => $id
    };
    $implements = [$class];
    foreach (class_implements($id) as $interface) {
      $implements[] = $interface;
    }
    yield from $implements;
  }
}
