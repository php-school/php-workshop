<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Factory;

use PhpSchool\PhpWorkshop\Listener\LazyContainerListener;
use Psr\Container\ContainerInterface;
use PhpSchool\PhpWorkshop\Event\EventDispatcher;
use PhpSchool\PhpWorkshop\Event\ContainerListenerHelper;
use PhpSchool\PhpWorkshop\Exception\InvalidArgumentException;
use PhpSchool\PhpWorkshop\ResultAggregator;

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
     * Merge the listener groups (group name => event name => listeners) in to a single array of event name => listeners.
     *
     * @param array<mixed> $listeners
     * @return array<string, array<mixed>>
     */
    private function mergeListenerGroups(array $listeners): array
    {
        $mergedListeners = [];

        foreach ($listeners as $events) {
            if (!is_array($events)) {
                throw InvalidArgumentException::typeMisMatch('array', $events);
            }

            foreach ($events as $event => $eventListeners) {
                if (!is_array($eventListeners)) {
                    throw InvalidArgumentException::typeMisMatch('array', $eventListeners);
                }

                $mergedListeners[(string) $event] = array_merge($mergedListeners[(string) $event] ?? [], $eventListeners);
            }
        }

        return $mergedListeners;
    }

    /**
     * @param string $eventName
     * @param array<mixed> $listeners
     * @param ContainerInterface $container
     * @param EventDispatcher $dispatcher
     * @throws InvalidArgumentException
     */
    private function attachListeners(
        string $eventName,
        array $listeners,
        ContainerInterface $container,
        EventDispatcher $dispatcher,
    ): void {
        array_walk($listeners, function ($listener) use ($eventName, $dispatcher, $container) {
            if ($listener instanceof ContainerListenerHelper) {
                if (!$container->has($listener->getService())) {
                    throw new InvalidArgumentException(
                        sprintf('Container has no entry named: "%s"', $listener->getService()),
                    );
                }

                $dispatcher->listen(
                    $eventName,
                    new LazyContainerListener($container, $listener),
                );
                return;
            }

            if (!is_callable($listener)) {
                throw new InvalidArgumentException(
                    'Listener must be a callable or a container entry for a callable service.',
                );
            }
            $dispatcher->listen($eventName, $listener);
        });
    }
}
