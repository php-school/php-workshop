<?php


namespace PhpWorkshop\PhpWorkshopTest\Command;

use MikeyMike\CliMenu\CliMenu;
use PHPUnit_Framework_TestCase;
use PhpWorkshop\PhpWorkshop\Command\MenuCommand;

/**
 * Class MenuCommandTest
 * @package PhpWorkshop\PhpWorkshop\Command
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
            ->method('display');

        $command = new MenuCommand($menu);
        $command->__invoke();
    }
}
