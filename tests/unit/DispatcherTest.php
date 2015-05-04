<?php

use MattFerris\Events\Dispatcher;
use MattFerris\Events\Event;

class DomainEventDispatcherTest extends \PHPUnit_Framework_TestCase
{
    public function testDispatchEvent()
    {
        $dispatcher = new Dispatcher();
        $event = new Event();

        $foo = null;
        $listener = function () use (&$foo) { $foo = 'bar'; };

        $dispatcher->addListener('MattFerris.Events.Event', $listener);
        $dispatcher->dispatch($event);

        $this->assertEquals($foo, 'bar');
    }

    /**
     * @depends testDispatchEvent
     */
    public function testWildcardListener()
    {
        $dispatcher = new Dispatcher();
        $event = new Event();

        $foo = 0;
        $listener = function () use (&$foo) { $foo++; };

        $dispatcher->addListener('*', $listener);
        $dispatcher->addListener('MattFerris.', $listener);
        $dispatcher->addListener('.Event', $listener);
        $dispatcher->dispatch($event);

        $this->assertEquals($foo, 3);
    }

    /**
     * @depends testDispatchEvent
     */
    public function testStopPropagation()
    {
        $dispatcher = new Dispatcher();
        $event = new Event();

        $bWasCalled = false;
        $listenerA = function () { return false; };
        $listenerB = function () use (&$bWasCalled) { $bWasCalled = true; };

        $dispatcher->addListener('*', $listenerA);
        $dispatcher->addListener('*', $listenerB);
        $dispatcher->dispatch($event);

        $this->assertFalse($bWasCalled);
    }
}

