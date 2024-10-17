<?php

declare(strict_types=1);

namespace DHP\components\service;

// TODO: Improve naming & aliasing of things!
/** @package DHP\components\service */
class Service
{
  private Scope $scope_storage;
  private ?array $prepareObject = null;

  public function __construct(?Scope $existingScope = null)
  {
    $this->scope_storage = match (true) {
      empty($existingScope) => new Scope(),
      default => $existingScope
    };
  }

  public function prepare(string $class): self
  {
    if ($this->prepareObject != null) {
      $this->store();
    }
    $this->prepareObject = [
      'type'    => 'singleton',
      'class'   => $class,
      'args'    => [],
      'aliases' => []
    ];
    return $this;
  }

  public function asTransient(): self
  {
    $this->prepareObject['type'] = 'transient';
    return $this;
  }

  public function withArgs(...$args): self
  {
    $this->prepareObject['args'] = $args;
    return $this;
  }

  public function withAliases(array $aliases): self
  {
    $this->prepareObject['aliases'] = $aliases;
    return $this;
  }

  public function store(): bool
  {
    $this->add(
      $this->prepareObject['type'],
      $this->prepareObject['class'],
      $this->prepareObject['aliases'],
      $this->prepareObject['args']
    );
    $this->prepareObject = null;
    return true;
  }

  // $service->prepareSingleton()->withArgs()->withAliases();



  public function clone()
  {
    return clone $this;
  }

  // TODO: Handle args provided
  public function load(string $class = null, ?array $args = null)
  {
    if ($class == null && $this->prepareObject != null) {
      $class = $this->prepareObject['class'];
      $this->store();
    }
    $obj = $this->scope_storage->get($class);
    if ($obj == STATE::NOT_SET && \class_exists($class)) {
      $this->addSingleton($class, [], $args);
      $obj = $this->scope_storage->get($class);
    }
    return $obj($args);
  }

  public function addSingleton(mixed $object, array $alias = [], ?array $args = null)
  {
    $this->add('singleton', $object, $alias, $args);
  }
  public function addTransient(mixed $object, array $alias = [], ?array $args = null)
  {
    $this->add('transient', $object, $alias, $args);
  }

  private function add(string $type, mixed $object, array $alias, ?array $args = null)
  {
    $proxy = match ($type) {
      "singleton" => new Singleton($this, $object, $args),
      "transient" => new Transient($this, $object, $args)
    };
    $aliases = $alias;
    foreach ($this->getAliases($object) as $alias) {
      $aliases[] = $alias;
    }
    $this->scope_storage->store($object, $proxy);
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
