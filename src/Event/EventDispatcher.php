<?php

namespace PhpSchool\PhpWorkshop\Event;

use PhpSchool\PhpWorkshop\Result\ResultInterface;
use PhpSchool\PhpWorkshop\ResultAggregator;

/**
 * An event dispatcher implementation.
 *
 * @package PhpSchool\PhpWorkshop\EventManager
 * @author  Aydin Hassan <aydin@hotmail.co.uk>
 */
class EventDispatcher
{
    /**
     * @var array
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
     * Dispatch an event. Can be any event object which implements `EventInterface`.
     *
     * @param EventInterface $event
     * @return EventInterface
     */
    public function dispatch(EventInterface $event)
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
     * same callback to multiple events or it can just be one event name as string.
     *
     * @param string|array $eventNames
     * @param callable $callback
     */
    public function listen($eventNames, callable $callback)
    {
        if (!is_array($eventNames)) {
            $eventNames = [$eventNames];
        }

        foreach ($eventNames as $eventName) {
            $this->attachListener($eventName, $callback);
        }
    }

    /**
     * @param string|array $eventName
     * @param callable $callback
     */
    private function attachListener($eventName, callable $callback)
    {
        if (!array_key_exists($eventName, $this->listeners)) {
            $this->listeners[$eventName] = [$callback];
        } else {
            $this->listeners[$eventName][] = $callback;
        }
    }

    /**
     * Insert a verifier callback which will execute at the given event name much like normal listeners.
     * A verifier should return an object which implements `PhpSchool\PhpWorkshop\Result\FailureInterface`
     * or `PhpSchool\PhpWorkshop\Result\SuccessInterface`. This result object will be added to the result aggregator.
     *
     * @param string|array $eventName
     * @param callable $verifier
     */
    public function insertVerifier($eventName, callable $verifier)
    {
        $this->attachListener($eventName, function (EventInterface $event) use ($verifier) {
            $result = $verifier($event);

            //return type hints pls
            if ($result instanceof ResultInterface) {
                $this->resultAggregator->add($result);
            } else {
                //??!!
            }
        });
    }
}
