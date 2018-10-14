<?php
/**
 * Created by Henrik Pejer ( mr@henrikpejer.com )
 * Date: 2018-10-06 17:55
 */

namespace DHP\kaerna\interfaces;

/**
 * Class EventInterface
 * @package DHP\kaerna\interfaces
 *
 * Event interface
 */
interface EventInterface
{
    public function register(string $event, callable $callable);

    public function trigger(string $event, &...$parameters);

    public function subscribe(\object $subscribeTarget, \object $subscriber);
}
