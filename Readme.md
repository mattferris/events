events
======

[![Build Status](https://travis-ci.org/mattferris/events.svg?branch=master)](https://travis-ci.org/mattferris/events)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/72507dd7-0606-4bc4-9a5b-63712283e031/mini.png)](https://insight.sensiolabs.com/projects/72507dd7-0606-4bc4-9a5b-63712283e031)

This is an events library with an included event logger. It's built with
[Udi Dahan's Domain Events pattern](http://www.udidahan.com/2008/08/25/domain-events-take-2/)
in mind.

Event Handling
--------------

Implementation starts with creating a `DomainEvents` class in your domain that
extends `MattFerris\Events\AbstractDomainEvents`.

```php
namespace MyDomain;

class DomainEvents extends \MattFerris\Events\AbstractDomainEvents
{
}
```

Then you can create individual event classes either by extending
`MattFerris\Events\Event` or implementing `MattFerris\Events\EventInterface`.
`Event` is an empty class so your event class will still need to provide it's
own logic.

```php
namespace MyDomain;

class SomeEvent extends MattFerris\Events\Event
{
    protected $someArgument;

    public function __construct($someArgument)
    {
        $this->someArgument = $someArgument;
    }

    public function getSomeArgument()
    {
        return $this->someArgument;
    }
}
```

To raise/dispatch an event from within your domain entities, call
`DomainEvents::dispatch()`.

```php
namespace MyDomain;

class SomeEntity
{
    public function doSomething()
    {
        // stuff happens

        // dispatch event
        DomainEvents::dispatch(new SomeEvent($argument));
    }
}
```

At the moment, there is no dispatcher configured so the event won't be handled
by anything. Configuring a dispatcher can be done like so:

```php
$dispatcher = new MattFerris\Events\Dispatcher();
MyDomain\DomainEvents::setDispatcher($dispatcher);
```

And now you can add events listeners to the dispatcher. Listeners can be any
`callable`, and can listen for a specific event or pattern matched event. To
match a specific event, use the fully qualified class name.

```php
$dispatcher->addListener('MyDomain.SomeEvent', function ($event) { ... });
```

It's also possible to listen for events based on a prefix or suffix.

```php
// listen for all events in the `MyDomain` namespace
$dispatcher->addListener('MyDomain.', $handler);

// listen for all SomeEvent events in any domain
$dispatcher->addListener('.SomeEvent', $handler);
```

Finally, you can listen for all events using an asterisk.

```php
// listen for all events
$dispatcher->addListener('*', $handler);
```

Event Names
-----------

For flexiblity, listeners can also be assigned to listen for arbitrary event
names. An event name can be passed to the dispatch method when the event is
dispatched.

```php
$dispatcher->addListener('foo.bar', $listener);

$dispatcher->dispatch($event, 'foo.bar');
```

Listener Priority
-----------------

By default, all listeners are given the same priority, and are called based on
the order they were added. Listeners can also be assigned a priority to ensure
they are called sooner or later then other listeners. The priority is an integer
that is passed when adding the listener. `0` is the highest priority.

```php
// give the listener the highest priority
$dispatcher->addListener('*', $listener, 0);
```

Priorities can also be assigned using the priority constants:

- `PRIORITY_HIGH` = 0
- `PRIORITY_NORMAL` = 50
- `PRIORITY_LOW` = 100

```php
// alternatively, you can use the priority constant
$dispatcher->addListener('*', $listener, Dispatcher::PRIORITY_HIGH);
```

Event Providers
---------------

Events can be added using providers. Simply create a class that implements
`MattFerris\Provider\ProviderInterface` and create a method called `provides()`.
When passed to the `register()` method on the dispatcher, the `provides()`
method will be passed an instance of the dispatcher, and you can then add
listeners.

```php
use MattFerris\Provider\ProviderInterface;
use MattFerris\Provider\ConsumerInterface;

class EventProvider implements ProviderInterface
{
    public function provides(ConsumerInterface $dispatcher)
    {
        $dispatcher->addListener('*', $listener);
        ...
    }
}
```

Event Logging
-------------

Event logging is accomplished by using `MattFerris\Events\Logger`. The
constructor takes an instance of `Dispatcher` and a callable that is passed the
resulting log message and can then write it to a log file, stderr, etc.

```php
$logger = new MattFerris\Events\Logger($dispatcher, function ($msg) { error_log($msg); });
```

That's it! All dispatched events will be logged to PHP's error log. By default,
log messages are just the name of the event prefixed with `event: `. For example:

```
event: MattFerris\Events\Event
```

You can change the prefix using `setPrefix()`.

```php
$logger->setPrefix('another prefix: ');
```

You can use logging helpers to customize the messages for certain events. Create
a class in your domain called `DomainEventLoggerHelpers` and have it extend
`MattFerris\Events\AbstractDomainEventLoggerHelpers`. Then simply create static
methods to be called for domain events, where each method returns the string to
be logged. These methods should start with `on` followed by the event name e.g.
`onSomeEvent`.

```php
namespace MyDomain;

class DomainEventLoggerHelpers extends MattFerris\Events\AbstractDomainEventLoggerHelpers
{
    static public function onSomeEvent(SomeEvent $e)
    {
        $foo = $e->getFoo();
        $bar = $e->getBar();

        return "SomeEvent was dispatched with values foo=$foo, bar=$bar";
    }
}
```

To register the helpers with the logger:

```php
MyDomain\DomainEventLoggerHelepers::addHelpers($logger);
```

Now, when `SomeEvent` is dispatched, the above helper will be called and the
returned string will be logged.

```
event: SomeEvent was dispatched with values foo=blah, bar=bleh
```

In my opinion, it makes sense to have these logging helpers defined in
`MyDomain\DomainEventLoggerHelpers` as the helpers are directly related to the
events in the domain. When domain events are updated, the logging helpers can be
updated and committed all together.
