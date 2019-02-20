<?php
/**
 * Created by Henrik Pejer ( mr@henrikpejer.com )
 * Date: 2018-10-06 17:55
 */

namespace DHP\components\module;

use DHP\components\container\ContainerInterface;

/**
 * Class ModuleInterface
 * @package DHP\kaerna\interfaces
 *
 * Some sort of basic class that all modules should extend.
 */
interface ModuleInterface
{
    /**
     * Helper method to trigger events.
     *
     * @param string $event
     * @param mixed ...$params
     * @return mixed
     */
    public function eventTrigger(string $event, &...$params);

    /**
     * Helper method to ease subscriptions on objects.
     *
     * @param object $object
     * @return mixed
     */
    public function subscribe(\object $object);

    public function setContainer(ContainerInterface $container);
}
