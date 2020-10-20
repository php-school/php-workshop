<?php

namespace PhpSchool\PhpWorkshopTest\Command;

use PhpSchool\CliMenu\CliMenu;
use PhpSchool\PhpWorkshop\Command\MenuCommandInvoker;
use PHPUnit\Framework\TestCase;

/**
 * Class MenuCommandInvokerTest
 * @package PhpSchool\PhpWorkshopTest\Command
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class MenuCommandInvokerTest extends TestCase
{
    public function testInvoker(): void
    {
        $menu = $this->createMock(CliMenu::class);
        $menu
            ->expects($this->once())
            ->method('close');
        
        $command = $this->getMockBuilder('stdClass')
            ->setMethods(['__invoke'])
            ->getMock();

        $command
            ->expects($this->once())
            ->method('__invoke');

        $invoker = new MenuCommandInvoker($command);
        $invoker->__invoke($menu);
    }
}
