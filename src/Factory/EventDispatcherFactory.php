<?php

namespace PhpSchool\PhpWorkshop\Factory;

use Interop\Container\ContainerInterface;
use PhpSchool\PhpWorkshop\Event\EventDispatcher;
use PhpSchool\PhpWorkshop\Exception\InvalidArgumentException;
use PhpSchool\PhpWorkshop\Listener\CodePatchListener;
use PhpSchool\PhpWorkshop\Listener\PrepareSolutionListener;
use PhpSchool\PhpWorkshop\Listener\SelfCheckListener;
use PhpSchool\PhpWorkshop\ResultAggregator;

/**
 * Class EventDispatcherFactory
 * @package PhpSchool\PhpWorkshop\Factory
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class EventDispatcherFactory
{

    /**
     * @param ContainerInterface $container
     * @return EventDispatcher
     * @throws InvalidArgumentException
     */
    public function __invoke(ContainerInterface $container)
    {
        $dispatcher = new EventDispatcher($container->get(ResultAggregator::class));

        $prepareSolutionListener = $container->get(PrepareSolutionListener::class);
        $dispatcher->listen('verify.start', $prepareSolutionListener);
        $dispatcher->listen('run.start', $prepareSolutionListener);

        $codePatcherListener = $container->get(CodePatchListener::class);
        $dispatcher->listen('verify.pre.execute', [$codePatcherListener, 'patch']);
        $dispatcher->listen('verify.post.execute', [$codePatcherListener, 'revert']);
        $dispatcher->listen('run.start', [$codePatcherListener, 'patch']);
        $dispatcher->listen('run.finish', [$codePatcherListener, 'revert']);

        $dispatcher->listen('verify.post.check', $container->get(SelfCheckListener::class));

        //add listeners from config
        $eventListeners = $container->has('eventListeners') ? $container->get('eventListeners') : [];

        if (!is_array($eventListeners)) {
            throw InvalidArgumentException::typeMisMatch('array', $eventListeners);
        }
        
        array_walk($eventListeners, function ($listeners, $eventName) use ($dispatcher, $container) {
            if (!is_array($listeners)) {
                throw InvalidArgumentException::typeMisMatch('array', $listeners);
            }

            $this->attachListeners($eventName, $listeners, $container, $dispatcher);
        });

        return $dispatcher;
    }

    /**
     * @param string $eventName
     * @param array $listeners
     * @param ContainerInterface $container
     * @param EventDispatcher $dispatcher
     * @throws \PhpSchool\PhpWorkshop\Exception\InvalidArgumentException
     */
    private function attachListeners(
        $eventName,
        array $listeners,
        ContainerInterface $container,
        EventDispatcher $dispatcher
    ) {
        array_walk($listeners, function ($listener) use ($eventName, $dispatcher, $container) {
            if (is_callable($listener)) {
                return $dispatcher->listen($eventName, $listener);
            }

            if (!is_string($listener)) {
                throw new InvalidArgumentException(
                    sprintf('Listener must be a callable or a container entry for a callable service.')
                );
            }

            if (!$container->has($listener)) {
                throw new InvalidArgumentException(sprintf('Container has no entry named: "%s"', $listener));
            }

            $listener = $container->get($listener);

            if (!is_callable($listener)) {
                throw InvalidArgumentException::typeMisMatch('callable', $listener);
            }

            return $dispatcher->listen($eventName, $listener);
        });
    }
}
