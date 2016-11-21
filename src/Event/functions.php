<?php

namespace PhpSchool\PhpWorkshop\Event;

if (!function_exists('PhpSchool\PhpWorkshop\Event\containerListener')) {

    /**
     * @param string $service
     * @param string $method
     * @return ContainerListenerHelper
     */
    function containerListener($service, $method = '__invoke')
    {
        return new ContainerListenerHelper($service, $method);
    }
}
