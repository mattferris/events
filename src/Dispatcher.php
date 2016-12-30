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
     * @var array
     */
    protected $listeners = array();

    /**
     * Add a listener
     *
     * @param string $name The event name
     * @param callable $listener The listener to add
     * @return void
     */
    public function addListener($name, callable $listener)
    {
        if (!array_key_exists($name, $this->listeners)) {
            $this->listeners[$name] = array();
        }
        $this->listeners[$name][] = $listener;
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
     * @return void
     */
    public function dispatch(EventInterface $event)
    {
        // isolate the class name
        $name = str_replace('\\', '.', get_class($event));

        $listeners = array();
        foreach (array_keys($this->listeners) as $pattern) {
            if ($pattern === '*') {
                $listeners = array_merge($listeners, $this->listeners[$pattern]);
            }
            if (substr($pattern, 0, 1) === '.') {
                if ($pattern === substr($name, strlen($pattern)*-1, strlen($pattern))) {
                    $listeners = array_merge($listeners, $this->listeners[$pattern]);
                }
            }
            if (substr($pattern, strlen($pattern)-1, 1) === '.') {
                if (strpos($name, $pattern) === 0) {
                    $listeners = array_merge($listeners, $this->listeners[$pattern]);
                }
            }
            if ($pattern === $name) {
                $listeners = array_merge($listeners, $this->listeners[$pattern]);
            }
        }

        foreach ($listeners as $listener) {
            if (call_user_func($listener, $event) === false) {
                // a handler stopped propagation
                break;
            }
        }
    }
}

