<?php

declare(strict_types=1);

namespace DHP\components\service;

class Singleton extends Proxy
{
  public function __invoke(): object
  {
    if (empty($this->obj)) {
      $this->obj = $this->instantiate();
    }
    return $this->obj;
  }
}
