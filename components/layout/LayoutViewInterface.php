<?php

namespace DHP\components\layout;

interface LayoutViewInterface
{

    public function render(string $template, array $args = [], $parent = null);
}
