<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Event;

if (!function_exists('PhpSchool\PhpWorkshop\Event\containerListener')) {

    /**
     * @param string $service
     * @param string $method
     * @return ContainerListenerHelper
     */
    function containerListener(string $service, string $method = '__invoke')
    {
        return new ContainerListenerHelper($service, $method);
    }
}
