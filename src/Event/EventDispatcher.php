<?php

namespace PhpSchool\PhpWorkshop\Event;

use Assert\Assertion;
use PhpSchool\PhpWorkshop\Result\ResultInterface;

/**
 * Class EventDispatcher
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
     * @var array
     */
    private $verifiers = [];

    /**
     * @var array
     */
    private $results = [];

    /**
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

        if (array_key_exists($event->getName(), $this->verifiers)) {
            foreach ($this->verifiers[$event->getName()] as $verifier) {
                $result = $verifier($event);

                //return type hints pls
                if ($result instanceof ResultInterface) {
                    $this->results[] = $result;
                } else {
                    //??!!
                }
            }
        }

        return $event;
    }

    /**
     * @param string $eventName
     * @param callable $callback
     */
    public function listen($eventName, callable $callback)
    {
        if (!array_key_exists($eventName, $this->listeners)) {
            $this->listeners[$eventName] = [$callback];
        } else {
            $this->listeners[$eventName][] = $callback;
        }
    }

    /**
     * @param string $eventName
     * @param callable $callback
     */
    public function insertVerifier($eventName, callable $callback)
    {
        if (!array_key_exists($eventName, $this->verifiers)) {
            $this->verifiers[$eventName] = [$callback];
        } else {
            $this->verifiers[$eventName][] = $callback;
        }
    }

    /**
     * @return array
     */
    public function getResults()
    {
        return $this->results;
    }
}
