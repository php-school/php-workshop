<?php

namespace PhpSchool\PhpWorkshopTest\Command;

use PhpSchool\PhpWorkshopTest\Asset\CallableStub;
use PhpSchool\CliMenu\CliMenu;
use PhpSchool\PhpWorkshop\Command\MenuCommandInvoker;
use PHPUnit\Framework\TestCase;

class MenuCommandInvokerTest extends TestCase
{
    public function testInvoker(): void
    {
        $menu = $this->createMock(CliMenu::class);
        $menu
            ->expects($this->once())
            ->method('close');

        $command = $this->createMock(CallableStub::class);

        $command
            ->expects($this->once())
            ->method('__invoke');

        $invoker = new MenuCommandInvoker($command);
        $invoker->__invoke($menu);
    }
}
