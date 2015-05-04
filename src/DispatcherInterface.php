<?php

/**
 * events - domain event library
 * www.bueller.ca/events
 *
 * DispatcherInterface.php
 * @copyright Copyright (c) 2015 Matt Ferris
 * @author Matt Ferris <matt@bueller.ca>
 *
 * Licensed under BSD 2-clause license
 * www.bueller.ca/events/license
 */

namespace MattFerris\Events;

interface DispatcherInterface
{
    /**
     * Dispatch an event
     *
     * @param EventInterface $event The event to dispatch
     * @return void
     */
    public function dispatch(EventInterface $event);
}

