<?php
/**
 * Created by Henrik Pejer ( mr@henrikpejer.com )
 * Date: 2018-10-07 10:55
 */

namespace DHP\components\event;

class Event implements EventInterface
{
    private $events = [];

    private $subscription = [];

    public function register(string $event, callable $callable)
    {
        $events    = $this->splitEvent($event);
        $eventBase = &$this->events;
        foreach ($events as $event) {
            if (!isset($eventBase[$event])) {
                $eventBase[$event] = ['_events' => []];
            }
            $eventBase = &$eventBase[$event];
        }
        $eventBase['_events'][] = $callable;
    }

    private function splitEvent($event)
    {
        return explode('.', $event);
    }

    public function trigger(string $event, &...$parameters)
    {
        $eventTargets = $this->matchEvents($event);
        foreach ($eventTargets as $target) {
            try {
                $target(...$parameters);
            } catch (\RuntimeException $exception) {
                break;
            }
        }
    }

    /**
     * This will return the events that match the given string.
     *
     * @param string $event
     * @return array|mixed
     */
    private function matchEvents(string $event, array $base = null)
    {
        $events = $this->splitEvent($event);
        if (empty($base)) {
            $base = &$this->events;
        }
        $return = [];
        $ln     = count($events);
        while ($ln > 0) {
            --$ln;
            $event = array_shift($events);
            if ($event == '*') { # Should we handle all events 'below' when * is the last one? Or just at this level...?
                $newBase = $base;
                unset($newBase['_events']);
                foreach ($newBase as $recursiveBase) {
                    $return = array_merge_recursive($return, $recursiveBase['_events']);
                    $return = array_merge_recursive($return, $this->matchEvents(implode('.', $events), $recursiveBase));
                }
                return $return;
                break;
            } else {
                if (!isset($base[$event])) {
                    return [];
                }
                $base = &$base[$event];
            }
        }
        $return = array_merge_recursive($return, $base['_events']);
        return $return;
    }

    public function subscribe(\object $subscribeTarget, \object $subscriber)
    {
        // TODO: Implement subscribe() method.
    }
}
