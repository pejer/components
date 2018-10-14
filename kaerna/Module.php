<?php
/**
 * Created by Henrik Pejer ( mr@henrikpejer.com )
 * Date: 2018-10-06 18:04
 */

namespace DHP\kaerna;

use DHP\kaerna\interfaces\ContainerInterface;
use DHP\kaerna\interfaces\ModuleInterface;

abstract class Module implements ModuleInterface
{
    /** @var ContainerInterface */
    private $container;

    /**
     * Helper method to trigger events.
     *
     * @param string $event
     * @param mixed ...$params
     * @return mixed
     */
    public function eventTrigger(string $event, &...$params)
    {
        return $this->container->get('\DHP\kaerna\interfaces\EventInterface')
            ->trigger($this, $event, ...$params);
    }

    /**
     * Helper method to ease subscriptions on objects.
     *
     * @param object $object
     * @return mixed
     */
    public function subscribe(\object $object)
    {
        return $this->container->get('\DHP\kaerna\interfaces\EventInterface')
            ->subscribe($object, $this);
    }

    /**
     * Set a container for the object.
     *
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
    }
}
