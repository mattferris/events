<?php

/**
 * events - domain event library
 * www.bueller.ca/events
 *
 * Logger.php
 * @copyright Copyright (c) 2015 Matt Ferris
 * @author Matt Ferris <matt@bueller.ca>
 *
 * Licensed under BSD 2-clause license
 * www.bueller.ca/events/license
 */

namespace MattFerris\Events;

class Logger implements LoggerInterface
{
    /**
     * @var DispatcherInterface
     */
    protected $dispatcher;

    /**
     * @var callable
     */
    protected $logger;

    /**
     * @var array
     */
    protected $helpers = array();

    /**
     * @var string
     */
    protected $prefix = 'event: ';

    /**
     * @param DispatcherInterface $dispatcher
     * @param callable $logger
     */
    public function __construct(DispatcherInterface $dispatcher, callable $logger = null)
    {
        $dispatcher->addListener('*', array($this, 'loggingListener'));

        if ($logger === null) {
            $logger = function ($msg) {
                error_log($msg);
            };
        }


        $this->addHelper('*', array($this, 'defaultHelper'));

        $this->dispatcher = $dispatcher;
        $this->logger = $logger;
    }

    /**
     * @param string $eventName
     * @param callable $helper
     */
    public function addHelper($eventName, callable $helper)
    {
        $this->helpers[$eventName] = $helper;
        return $this;
    }

    /**
     * @param string $prefix
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
        return $this;
    }

    /**
     * @param EventInterface $event
     */
    public function loggingListener(EventInterface $event)
    {
        $eventName = get_class($event);

        $msg = null;
        if (isset($this->helpers[$eventName]) || array_key_exists($eventName, $this->helpers)) {
            $msg = call_user_func($this->helpers[$eventName], $event);
        } else {
            $msg = call_user_func($this->helpers['*'], $event);
        }

        call_user_func($this->logger, $this->prefix.$msg);
    }

    /**
     * @param EventInterface $event
     */
    protected function defaultHelper(EventInterface $event)
    {
        return get_class($event);
    }
}

