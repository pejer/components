<?php

declare(strict_types=1);

namespace DHP\components;

class Fluid
{
  protected $ts;
  public function __construct()
  {
    $this->ts = time();
  }

  public function heck()
  {
    var_dump($this->ts);
  }
}
