<?php

declare(strict_types=1);

namespace DHP\components\service;

/** @package DHP\components\service */
class Singleton extends Proxy
{
  public function __invoke(?array $args = null): object
  {
    if (empty($this->obj)) {
      if (!empty($args)) {
        $this->args = $args;
      }
      $this->obj = $this->instantiate();
    }
    return $this->obj;
  }
}
