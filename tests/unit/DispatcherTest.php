<?php

use MattFerris\Events\Dispatcher;
use MattFerris\Events\Event;
use MattFerris\Provider\ProviderInterface;

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

    /**
     * @depends testDispatchEvent
     */
    public function testNamedEventListener()
    {
        $dispatcher = new Dispatcher();
        $event = new Event();

        $foo = false;
        $listener = function () use (&$foo) { $foo = true; };

        $dispatcher->addListener('bar.baz', $listener);
        $dispatcher->dispatch($event);
        $this->assertFalse($foo);

        $dispatcher->dispatch($event, 'bar.baz');
        $this->assertTrue($foo);
    }

    /**
     * @depends testDispatchEvent
     * @depends testStopPropagation
     */
    public function testPrioritizedEventListener()
    {
        $dispatcher = new Dispatcher();

        $aWasCalled = $bWasCalled = false;
        $listenerA = function () use (&$aWasCalled) { $aWasCalled = true; };
        $listenerB = function () use (&$bWasCalled) { $bWasCalled = true; return false; };

        $dispatcher->addListener('*', $listenerA);
        $dispatcher->addListener('*', $listenerB, Dispatcher::PRIORITY_HIGH);

        $dispatcher->dispatch(new Event());
        $this->assertEquals(Dispatcher::PRIORITY_HIGH, 0);
        $this->assertFalse($aWasCalled);
        $this->assertTrue($bWasCalled);

        $dispatcher = new Dispatcher();

        $foo = 0;
        $atA = $atB = $atC = false;

        $listenerA = function () use (&$foo, &$atA) { $atA = $foo++; };
        $listenerB = function () use (&$foo, &$atB) { $atB = $foo++; };
        $listenerC = function () use (&$foo, &$atC) { $atC = $foo++; };

        $dispatcher->addListener('*', $listenerA, Dispatcher::PRIORITY_LOW);
        $dispatcher->addListener('*', $listenerB, Dispatcher::PRIORITY_HIGH);
        $dispatcher->addListener('*', $listenerC, Dispatcher::PRIORITY_NORMAL);

        $dispatcher->dispatch(new Event());

        $this->assertEquals(Dispatcher::PRIORITY_NORMAL, 50);
        $this->assertEquals(Dispatcher::PRIORITY_LOW, 100);
        $this->assertEquals($atA, 2);
        $this->assertEquals($atB, 0);
        $this->assertEquals($atC, 1);
    }

    public function testBundleRegistration()
    {
        $dispatcher = new Dispatcher();

        $bundle = $this->getMockBuilder('\MattFerris\Provider\ProviderInterface')
            ->setMethods(['provides'])
            ->getMock();

        $bundle->expects($this->once())
            ->method('provides')
            ->with($dispatcher);

        $this->assertEquals($dispatcher->register($bundle), $dispatcher);
    }
}

