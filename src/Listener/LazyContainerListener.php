<?php

namespace PhpSchool\PhpWorkshop\Listener;

use PhpSchool\PhpWorkshop\Event\ContainerListenerHelper;
use PhpSchool\PhpWorkshop\Exception\InvalidArgumentException;
use Psr\Container\ContainerInterface;

class LazyContainerListener
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var ContainerListenerHelper
     */
    private $listener;

    public function __construct(ContainerInterface $container, ContainerListenerHelper $listener)
    {
        $this->container = $container;
        $this->listener = $listener;
    }

    /**
     * @param mixed ...$args
     */
    public function __invoke(...$args): void
    {
        /** @var object $service */
        $service = $this->container->get($this->listener->getService());

        if (!method_exists($service, $this->listener->getMethod())) {
            throw new InvalidArgumentException(
                sprintf('Method "%s" does not exist on "%s"', $this->listener->getMethod(), get_class($service))
            );
        }

        $service->{$this->listener->getMethod()}(...$args);
    }

    /**
     * @return callable
     */
    public function getWrapped(): callable
    {
        /** @var callable $listener */
        $listener =  [
            $this->container->get($this->listener->getService()),
            $this->listener->getMethod()
        ];

        return $listener;
    }
}
