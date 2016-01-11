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
     * @param string $eventName
     * @param array $eventArgs
     */
    public function dispatch($eventName, array $eventArgs = [])
    {
        if (array_key_exists($eventName, $this->listeners)) {
            foreach ($this->listeners[$eventName] as $listener) {
                $listener(...$eventArgs);
            }
        }

        if (array_key_exists($eventName, $this->verifiers)) {
            foreach ($this->verifiers[$eventName] as $verifier) {
                $result = $verifier(...$eventArgs);

                //return type hints pls
                if ($result instanceof ResultInterface) {
                    $this->results[] = $result;
                } else {
                    //??!!
                }
            }
        }
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
