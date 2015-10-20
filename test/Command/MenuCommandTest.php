<?php


namespace PhpSchool\PhpWorkshopTest\Command;

use PhpSchool\CliMenu\CliMenu;
use PHPUnit_Framework_TestCase;
use PhpSchool\PhpWorkshop\Command\MenuCommand;

/**
 * Class MenuCommandTest
 * @package PhpSchool\PhpWorkshop\Command
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class MenuCommandTest extends PHPUnit_Framework_TestCase
{
    public function testInvoke()
    {
        $menu = $this->getMockBuilder(CliMenu::class)
            ->disableOriginalConstructor()
            ->getMock();

        $menu
            ->expects($this->once())
            ->method('open');

        $command = new MenuCommand($menu);
        $command->__invoke();
    }
}
