<?php

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
        $dispatcher = new EventDispatcher($container->get(ResultAggregator::class));

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
     * @param array<int, array<string, array>> $listeners
     * @return array<int, array>
     */
    private function mergeListenerGroups(array $listeners): array
    {
        $listeners = new Collection($listeners);

        return $listeners
            ->keys()
            ->reduce(function (Collection $carry, $listenerGroup) use ($listeners) {
                $events = new Collection($listeners->get($listenerGroup));

                return $events
                    ->keys()
                    ->reduce(function (Collection $carry, $event) use ($events) {
                        $listeners = $events->get($event);

                        if (!is_array($listeners)) {
                            throw InvalidArgumentException::typeMisMatch('array', $listeners);
                        }

                        return $carry->set(
                            $event,
                            array_merge($carry->get($event, []), $listeners)
                        );
                    }, $carry);
            }, new Collection())
            ->getArrayCopy();
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
