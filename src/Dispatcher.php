<?php

/**
 * events - domain event library
 * www.bueller.ca/events
 *
 * Dispatcher.php
 * @copyright Copyright (c) 2015 Matt Ferris
 * @author Matt Ferris <matt@bueller.ca>
 *
 * Licensed under BSD 2-clause license
 * www.bueller.ca/events/license
 */

namespace MattFerris\Events;

use MattFerris\Provider\ProviderInterface;
use MattFerris\Provider\ConsumerInterface;

class Dispatcher implements DispatcherInterface, ConsumerInterface
{
    /**
     * @const int
     */
    const PRIORITY_HIGH = 0;

    /**
     * @const int
     */
    const PRIORITY_NORMAL = 50;

    /**
     * @const int
     */
    const PRIORITY_LOW = 100;

    /**
     * @var array
     */
    protected $listeners = array();

    /**
     * Add a listener
     *
     * @param string $name The event name
     * @param callable $listener The listener to add
     * @param int $priority The  priority of the listener (default is 50)
     * @return void
     */
    public function addListener($name, callable $listener, $priority = self::PRIORITY_NORMAL)
    {
        // convert blackslashes in namespaces to dots
        $name = str_replace('\\', '.', $name);

        if (!array_key_exists($priority, $this->listeners)) {
            $this->listeners[$priority] = array();
        }
        if (!array_key_exists($name, $this->listeners[$priority])) {
            $this->listeners[$priority][$name] = array();
        }
        $this->listeners[$priority][$name][] = $listener;
    }

    /**
     * Register a bundle
     *
     * @param \MattFerris\Provider\ProviderInterface $bundle The bundle to register
     * @return self
     */
    public function register(ProviderInterface $bundle)
    {
        $bundle->provides($this);
        return $this;
    }

    /**
     * Dispatch an event
     *
     * @param EventInterface $event The event to dispatch
     * @param string $name The name of the event (optional, default is event class)
     * @return void
     */
    public function dispatch(EventInterface $event, $name = null)
    {
        // if no $name is specified, use $event's class name
        if (is_null($name)) {
            // isolate the class name
            $name = str_replace('\\', '.', get_class($event));
        }

        // loop through priorities
        ksort($this->listeners);
        foreach (array_keys($this->listeners) as $priority) {
            $listeners = array();

            // loop through patterns for priority
            foreach (array_keys($this->listeners[$priority]) as $pattern) {
                $patternListeners = $this->listeners[$priority][$pattern];

                // wildcard listeners are always added
                if ($pattern === '*') {
                    $listeners = array_merge($listeners, $patternListeners);
                }

                // add listeners if suffix matches (*.something)
                if (substr($pattern, 0, 1) === '.') {
                    if ($pattern === substr($name, strlen($pattern)*-1, strlen($pattern))) {
                        $listeners = array_merge($listeners, $patternListeners);
                    }
                }

                // add listeners if prefix matches (something.*)
                if (substr($pattern, strlen($pattern)-1, 1) === '.') {
                    if (strpos($name, $pattern) === 0) {
                        $listeners = array_merge($listeners, $patternListeners);
                    }
                }

                // add listeners if pattern matches name
                if ($pattern === $name) {
                    $listeners = array_merge($listeners, $patternListeners);
                }
            }

            // call all collected listeners
            foreach ($listeners as $listener) {
                if (call_user_func($listener, $event) === false) {
                    // a handler stopped propagation
                    break(2);
                }
            }

        }
    }
}

