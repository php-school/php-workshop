<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Event;

use DI\Definition\Helper\CreateDefinitionHelper;

use function DI\create;

if (!function_exists('PhpSchool\PhpWorkshop\Event\containerListener')) {

    /**
     * A container definition rather than a closure, so containers that use it can be compiled.
     */
    function containerListener(string $service, string $method = '__invoke'): CreateDefinitionHelper
    {
        return create(ContainerListenerHelper::class)->constructor($service, $method);
    }
}
