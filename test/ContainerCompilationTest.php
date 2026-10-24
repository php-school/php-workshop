<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshopTest;

use DI\ContainerBuilder;
use PhpSchool\PhpWorkshop\Event\EventDispatcher;

class ContainerCompilationTest extends BaseTest
{
    public function testConfigCanBeCompiled(): void
    {
        $containerBuilder = new ContainerBuilder();
        $containerBuilder->addDefinitions(['basePath' => __DIR__ . '/..']);
        $containerBuilder->addDefinitions(require __DIR__ . '/../app/config.php');
        $containerBuilder->enableCompilation($this->getTemporaryDirectory(), 'CompiledWorkshopContainer');

        $container = $containerBuilder->build();

        $this->assertInstanceOf(EventDispatcher::class, $container->get(EventDispatcher::class));
    }
}
