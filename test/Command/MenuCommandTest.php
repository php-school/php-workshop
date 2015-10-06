<?php


namespace PhpWorkshop\PhpWorkshopTest\Command;

use PHPUnit_Framework_TestCase;
use PhpWorkshop\PhpWorkshop\Command\MenuCommand;
use PhpWorkshop\PhpWorkshop\Menu;

/**
 * Class MenuCommandTest
 * @package PhpWorkshop\PhpWorkshop\Command
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class MenuCommandTest extends PHPUnit_Framework_TestCase
{
    public function testInvoke()
    {
        $menu = $this->getMockBuilder(Menu::class)
            ->disableOriginalConstructor()
            ->getMock();

        $menu
            ->expects($this->once())
            ->method('display');

        $command = new MenuCommand($menu);
        $command->__invoke();
    }
}
