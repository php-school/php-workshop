<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Event;

if (!function_exists('PhpSchool\PhpWorkshop\Event\containerListener')) {

    function containerListener(string $service, string $method = '__invoke'): callable
    {
        return fn () => new ContainerListenerHelper($service, $method);
    }
}
