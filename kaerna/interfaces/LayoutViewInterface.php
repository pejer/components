<?php

namespace DHP\kaerna\interfaces;

interface LayoutViewInterface
{

    public function render(string $template, array $args = [], $parent = null);
}
