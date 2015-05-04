<?php

/**
 * events - domain event library
 * www.bueller.ca/events
 *
 * LoggerInterface.php
 * @copyright Copyright (c) 2015 Matt Ferris
 * @author Matt Ferris <matt@bueller.ca>
 *
 * Licensed under BSD 2-clause license
 * www.bueller.ca/events/license
 */

namespace MattFerris\Events;

interface LoggerInterface
{
    /**
     * @param string $eventName
     * @param callable $helper
     */
    public function addHelper($eventName, callable $helper);

    /**
     * @param string $prefix
     */
    public function setPrefix($prefix);

    /**
     * @param EventInterface $event
     */
    public function loggingListener(EventInterface $event);
}

