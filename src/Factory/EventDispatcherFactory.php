<?php

namespace PhpSchool\PhpWorkshop\Factory;

use Interop\Container\ContainerInterface;
use PhpSchool\PhpWorkshop\Event\EventDispatcher;
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
     */
    public function __invoke(ContainerInterface $container)
    {
        $dispatcher = new EventDispatcher($container->get(ResultAggregator::class));

        $dispatcher->listen('verify.start', $container->get(PrepareSolutionListener::class));

        $codePatcherListener = $container->get(CodePatchListener::class);
        $dispatcher->listen('verify.pre.execute', [$codePatcherListener, 'patch']);
        $dispatcher->listen('verify.post.execute', [$codePatcherListener, 'revert']);

        $dispatcher->listen('verify.post.check', $container->get(SelfCheckListener::class));

        return $dispatcher;
    }
}
