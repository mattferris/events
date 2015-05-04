<?php

/**
 * events - domain event library
 * www.bueller.ca/events
 *
 * AbstractDomainEvents.php
 * @copyright Copyright (c) 2015 Matt Ferris
 * @author Matt Ferris <matt@bueller.ca>
 *
 * Licensed under BSD 2-clause license
 * www.bueller.ca/events/license
 */

namespace MattFerris\Events;

abstract class AbstractDomainEvents
{
    protected static $dispatcher = null;

    /**
     * Set a dispatcher
     *
     * @param DispatcherInterface $dispatcher The delegate for the events
     * @return void
     */
    public static function setDispatcher(DispatcherInterface $dispatcher)
    {
        self::$dispatcher = $dispatcher;
    }

    /**
     * Dispatch an event
     *
     * @param EventInterface $event The event to raise
     * @return void
     */
    public static function dispatch(EventInterface $event)
    {
        if (self::$dispatcher !== null) {
            self::$dispatcher->dispatch($event);
        }
    }
}

