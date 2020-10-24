<?php

namespace PhpSchool\PhpWorkshop\Event;

/**
 * A utility to reference listeners in the container
 */
class ContainerListenerHelper
{
    /**
     * @var string
     */
    private $service;

    /**
     * @var string
     */
    private $method;

    /**
     * @param string $service
     * @param string $method
     */
    public function __construct(string $service, string $method = '__invoke')
    {
        $this->service = $service;
        $this->method = $method;
    }

    /**
     * @return string
     */
    public function getService(): string
    {
        return $this->service;
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }
}
