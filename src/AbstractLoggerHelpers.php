<?php

/**
 * events - domain event library
 * www.bueller.ca/events
 *
 * AbstractLoggerHelpers.php
 * @copyright Copyright (c) 2015 Matt Ferris
 * @author Matt Ferris <matt@bueller.ca>
 *
 * Licensed under BSD 2-clause license
 * www.bueller.ca/events/license
 */

namespace MattFerris\Events;

abstract class AbstractLoggerHelpers
{
    /**
     * @param LoggerInterface
     */
    static public function addHelpers(LoggerInterface $logger)
    {
        $self = get_called_class();
        $ref = new \ReflectionClass($self);
        $methods = $ref->getMethods(\ReflectionMethod::IS_STATIC);

        $classParts = explode('\\', $self);
        array_pop($classParts);
        $namespace = implode('\\', $classParts);

        foreach ($methods as $method) {
            $name = $method->getName();
            if (strpos($name, 'on') === 0) {
                $eventName = substr($name, 2);
                $eventClass = $namespace.'\\'.$eventName;
                $logger->addHelper($eventClass, array($self, $name));
            }
        }
    }
}

