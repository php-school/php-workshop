<?php


namespace PhpSchool\PhpWorkshopTest\Command;

use PhpSchool\CliMenu\CliMenu;
use PHPUnit\Framework\TestCase;
use PhpSchool\PhpWorkshop\Command\MenuCommand;

/**
 * Class MenuCommandTest
 * @package PhpSchool\PhpWorkshop\Command
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class MenuCommandTest extends TestCase
{
    public function testInvoke() : void
    {
        $menu = $this->createMock(CliMenu::class);
        $menu
            ->expects($this->once())
            ->method('open');

        $command = new MenuCommand($menu);
        $command->__invoke();
    }
}
