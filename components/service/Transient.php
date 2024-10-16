<?php

declare(strict_types=1);

namespace DHP\components\service;

class Transient extends Proxy
{

  public function __invoke(): object
  {
    return $this->instantiate();
  }
}
