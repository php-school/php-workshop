<?php

namespace PhpSchool\PhpWorkshop\Factory;

use Interop\Container\ContainerInterface;
use PhpSchool\PhpWorkshop\Event\EventDispatcher;

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
     */
    public function __invoke(ContainerInterface $container)
    {
        $dispatcher = new EventDispatcher;

        if ($container->has('coreListeners')) {
            foreach ($container->get('coreListeners') as $eventName => $listener) {
                $dispatcher->listen($eventName, $container->get($listener));
            }
        }

        return $dispatcher;
    }
}
