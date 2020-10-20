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
     * @param $service
     * @param string $method
     */
    public function __construct($service, $method = '__invoke')
    {
        $this->service = $service;
        $this->method = $method;
    }

    /**
     * @return string
     */
    public function getService()
    {
        return $this->service;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }
}
