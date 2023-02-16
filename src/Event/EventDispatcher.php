<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Event;

use PhpSchool\PhpWorkshop\Result\ResultInterface;
use PhpSchool\PhpWorkshop\ResultAggregator;

/**
 * An event dispatcher implementation.
 */
class EventDispatcher
{
    /**
     * @var array<string, array<callable>>
     */
    private $listeners = [];

    /**
     * @var ResultAggregator
     */
    private $resultAggregator;

    /**
     * @param ResultAggregator $resultAggregator
     */
    public function __construct(ResultAggregator $resultAggregator)
    {
        $this->resultAggregator = $resultAggregator;
    }

    /**
     * Dispatch an event. Can be any event object which implements `PhpSchool\PhpWorkshop\Event\EventInterface`.
     *
     * @param EventInterface $event
     * @return EventInterface
     */
    public function dispatch(EventInterface $event): EventInterface
    {
        if (array_key_exists($event->getName(), $this->listeners)) {
            foreach ($this->listeners[$event->getName()] as $listener) {
                $listener($event);
            }
        }

        return $event;
    }

    /**
     * Attach a callback to an event name. `$eventNames` can be an array of event names in order to attach the same
     * callback to multiple events or it can just be one event name as a string.
     *
     * @param string|array<string> $eventNames
     * @param callable $callback
     */
    public function listen($eventNames, callable $callback): void
    {
        if (!is_array($eventNames)) {
            $eventNames = [$eventNames];
        }

        foreach ($eventNames as $eventName) {
            $this->attachListener($eventName, $callback);
        }
    }

    /**
     * @param string $eventName
     * @param callable $callback
     */
    private function attachListener(string $eventName, callable $callback): void
    {
        if (!array_key_exists($eventName, $this->listeners)) {
            $this->listeners[$eventName] = [$callback];
        } else {
            $this->listeners[$eventName][] = $callback;
        }
    }

    public function removeListener(string $eventName, callable $callback): void
    {
        foreach ($this->listeners[$eventName] ?? [] as $key => $listener) {
            if ($listener === $callback) {
                unset($this->listeners[$eventName][$key]);
                $this->listeners[$eventName] = array_values($this->listeners[$eventName]);

                if (empty($this->listeners[$eventName])) {
                    unset($this->listeners[$eventName]);
                }

                break;
            }
        }
    }

    /**
     * Insert a verifier callback which will execute at the given event name much like normal listeners.
     * A verifier should return an object which implements `PhpSchool\PhpWorkshop\Result\FailureInterface`
     * or `PhpSchool\PhpWorkshop\Result\SuccessInterface`. This result object will be added to the result aggregator.
     *
     * @param string $eventName
     * @param callable $verifier
     */
    public function insertVerifier(string $eventName, callable $verifier): void
    {
        $this->attachListener($eventName, function (EventInterface $event) use ($verifier) {
            $result = $verifier($event);

            //return type hints pls
            if ($result instanceof ResultInterface) {
                $this->resultAggregator->add($result);
            }
        });
    }

    /**
     * @return array<string, array<callable>>
     */
    public function getListeners(): array
    {
        return $this->listeners;
    }
}
