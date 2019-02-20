<?php

namespace DHP\components\layout;

/**
 *  Interface LayoutInterface
 *
 * This is a bridge between the layout engine you choose and the rest of the application.
 * @package DHP\kaerna\interface
 */
interface LayoutInterface extends ServiceInterface
{
    /**
     * LayoutInterface constructor.
     *
     * @param array $config the configuration for the layout engine.
     */
    public function __construct(array $config, EventInterface $event);

    /**
     * This will render the template with the provided data.
     *
     * @param string $template
     * @param array  $values
     * @return mixed
     */
    public function render(string $template, array $values);
}