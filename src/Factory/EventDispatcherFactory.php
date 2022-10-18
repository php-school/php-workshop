<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Factory;

use Psr\Container\ContainerInterface;
use PhpSchool\PhpWorkshop\Event\EventDispatcher;
use PhpSchool\PhpWorkshop\Event\ContainerListenerHelper;
use PhpSchool\PhpWorkshop\Exception\InvalidArgumentException;
use PhpSchool\PhpWorkshop\ResultAggregator;
use PhpSchool\PhpWorkshop\Utils\Collection;

/**
 * Create an EventDispatcher populating with listeners from the config
 */
class EventDispatcherFactory
{
    /**
     * @param ContainerInterface $container
     * @return EventDispatcher
     * @throws InvalidArgumentException
     */
    public function __invoke(ContainerInterface $container): EventDispatcher
    {
        /** @var ResultAggregator $results */
        $results = $container->get(ResultAggregator::class);
        $dispatcher = new EventDispatcher($results);

        //add listeners from config
        $eventListeners = $container->has('eventListeners') ? $container->get('eventListeners') : [];

        if (!is_array($eventListeners)) {
            throw InvalidArgumentException::typeMisMatch('array', $eventListeners);
        }

        array_walk($eventListeners, function ($events) {
            if (!is_array($events)) {
                throw InvalidArgumentException::typeMisMatch('array', $events);
            }
        });

        $eventListeners = $this->mergeListenerGroups($eventListeners);

        array_walk($eventListeners, function ($listeners, $eventName) use ($dispatcher, $container) {
            $this->attachListeners($eventName, $listeners, $container, $dispatcher);
        });

        return $dispatcher;
    }

    /**
     * @param array<int, array<string, array<ContainerListenerHelper|callable>>> $listeners
     * @return array<string, array<ContainerListenerHelper|callable>>
     */
    private function mergeListenerGroups(array $listeners): array
    {
        $listeners = new Collection($listeners);

        /** @var Collection<string, array<ContainerListenerHelper|callable>> $mergedListeners */
        $mergedListeners = $listeners
            ->keys()
            ->reduce(function (Collection $carry, string $listenerGroup) use ($listeners): Collection {
                /** @var array<string, array<ContainerListenerHelper|callable>> $groupListeners */
                $groupListeners = $listeners->get($listenerGroup);
                $events = new Collection($groupListeners);

                return $events
                    ->keys()
                    ->reduce(function (Collection $carry, string $event) use ($events) {
                        /** @var Collection<string, array<ContainerListenerHelper|callable>> $carry */
                        $listeners = $events->get($event);

                        if (!is_array($listeners)) {
                            throw InvalidArgumentException::typeMisMatch('array', $listeners);
                        }

                        return $carry->set(
                            $event,
                            array_merge($carry->get($event, []), $listeners)
                        );
                    }, $carry);
            }, new Collection());

            return $mergedListeners->getArrayCopy();
    }

    /**
     * @param string $eventName
     * @param array<int, ContainerListenerHelper|callable> $listeners
     * @param ContainerInterface $container
     * @param EventDispatcher $dispatcher
     * @throws InvalidArgumentException
     */
    private function attachListeners(
        string $eventName,
        array $listeners,
        ContainerInterface $container,
        EventDispatcher $dispatcher
    ): void {
        array_walk($listeners, function ($listener) use ($eventName, $dispatcher, $container) {
            if ($listener instanceof ContainerListenerHelper) {
                if (!$container->has($listener->getService())) {
                    throw new InvalidArgumentException(
                        sprintf('Container has no entry named: "%s"', $listener->getService())
                    );
                }

                $dispatcher->listen($eventName, function (...$args) use ($container, $listener) {
                    /** @var object $service */
                    $service = $container->get($listener->getService());

                    if (!method_exists($service, $listener->getMethod())) {
                        throw new InvalidArgumentException(
                            sprintf('Method "%s" does not exist on "%s"', $listener->getMethod(), get_class($service))
                        );
                    }

                    $service->{$listener->getMethod()}(...$args);
                });
                return;
            }

            if (!is_callable($listener)) {
                throw new InvalidArgumentException(
                    sprintf('Listener must be a callable or a container entry for a callable service.')
                );
            }
            $dispatcher->listen($eventName, $listener);
        });
    }
}
