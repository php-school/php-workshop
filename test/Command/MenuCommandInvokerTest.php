<?php

namespace PhpSchool\PhpWorkshopTest\Command;

use PhpSchool\CliMenu\CliMenu;
use PhpSchool\PhpWorkshop\Command\MenuCommandInvoker;
use PHPUnit_Framework_TestCase;

/**
 * Class MenuCommandInvokerTest
 * @package PhpSchool\PhpWorkshopTest\Command
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class MenuCommandInvokerTest extends PHPUnit_Framework_TestCase
{
    public function testInvoker()
    {
        $menu = $this->getMockBuilder(CliMenu::class)
            ->disableOriginalConstructor()
            ->getMock();
    
        $menu
            ->expects($this->once())
            ->method('close');
        
        $command = $this->getMock('stdClass', ['myCallBack']);
        $command
            ->expects($this->once())
            ->method('myCallBack');
        
        $invoker = new MenuCommandInvoker([$command, 'myCallBack']);
        $invoker->__invoke($menu);
    }
}
