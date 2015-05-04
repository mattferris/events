<?php

use MattFerris\Events\Logger;

class LoggerTest extends PHPUnit_Framework_TestCase
{
    protected $dispatcher;
    protected $stub;

    public function setup()
    {
        $this->dispatcher = $this->getMockBuilder('MattFerris\Events\Dispatcher')
            ->disableOriginalConstructor()
            ->setMethods(array('addListener'))
            ->getMock();

        $this->stub = $this->getMockBuilder('LoggerStub')->getMock();
    }

    public function testLogEvent()
    {
        $this->dispatcher
            ->expects($this->once())
            ->method('addListener')
            ->with('*', $this->callback(function ($arg) {
                return ($arg[0] instanceof Logger && $arg[1] === 'loggingListener');
            }));

        $this->stub
            ->expects($this->exactly(3))
            ->method('loggingListener')
            ->withConsecutive(
                array('event: MattFerris\Events\Event'),
                array('foo: MattFerris\Events\Event'),
                array('foo: bar')
             );

        $logger = new Logger($this->dispatcher, array($this->stub, 'loggingListener'));
        $logger->loggingListener(new MattFerris\Events\Event());

        // test setting prefix
        $logger->setPrefix('foo: ')->loggingListener(new MattFerris\Events\Event());

        // test with helper
        $logger->addHelper('MattFerris\\Events\\Event', function ($e) {
            return 'bar';
        });
        $logger->loggingListener(new MattFerris\Events\Event());
    }
}

class LoggerStub
{
    public function loggingListener()
    {
    }
}

