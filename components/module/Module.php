<?php
/**
 * Created by Henrik Pejer ( mr@henrikpejer.com )
 * Date: 2018-10-06 18:04
 */

namespace DHP\components\module;

use DHP\kaerna\interfaces\ContainerInterface;

abstract class Module implements ModuleInterface
{
    /** @var ContainerInterface */
    private $container;

    /**
     * Helper method to trigger events.
     *
     * @param string $event     The event to trigger
     * @param mixed  ...$params The params that should be provided
     *
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
     * @param object $object The object to subscribe to
     *
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
     * @param ContainerInterface $container The container to use.
     *
     * @return null
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
    }
}
