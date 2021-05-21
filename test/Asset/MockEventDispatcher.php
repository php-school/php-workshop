<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshopTest\Asset;

use PhpSchool\PhpWorkshop\Event\EventDispatcher;
use PhpSchool\PhpWorkshop\Event\EventInterface;

class MockEventDispatcher extends EventDispatcher
{
    private $dispatches = [];
    private $listeners = [];

    public function __construct()
    {
        // noop
    }

    public function dispatch(EventInterface $event): EventInterface
    {
        isset($this->dispatches[$event->getName()])
            ? $this->dispatches[$event->getName()]++
            : $this->dispatches[$event->getName()] = 1;

        return $event;
    }

    public function listen($eventNames, callable $callback): void
    {
        if (!is_array($eventNames)) {
            $eventNames = [$eventNames];
        }

        foreach ($eventNames as $eventName) {
            isset($this->listeners[$eventName])
                ? $this->listeners[$eventName][] = $callback
                : $this->listeners[$eventName] = [$callback];
        }
    }

    public function getEventDispatchCount(string $eventName): int
    {
        return $this->dispatches[$eventName] ?? 0;
    }

    public function getEventListeners(string $eventName): array
    {
        return $this->listeners[$eventName] ?? [];
    }
}
