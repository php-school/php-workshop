<?php

namespace PhpSchool\PhpWorkshopTest\MenuItem;

use PhpSchool\CliMenu\CliMenu;
use PhpSchool\CliMenu\Dialogue\Confirm;
use PhpSchool\CliMenu\MenuItem\MenuItemInterface;
use PhpSchool\CliMenu\MenuStyle;
use PhpSchool\Terminal\Terminal;
use PhpSchool\PhpWorkshop\MenuItem\ResetProgress;
use PhpSchool\PhpWorkshop\Output\OutputInterface;
use PhpSchool\PhpWorkshop\UserState;
use PhpSchool\PhpWorkshop\UserStateSerializer;
use PHPUnit\Framework\TestCase;

/**
 * Class ResetProgressTest
 * @package PhpSchool\PhpWorkshopTest\MenuItem
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class ResetProgressTest extends TestCase
{
    public function testResetProgressDisablesParentMenuItems() : void
    {
        $item1 = $this->createMock(MenuItemInterface::class);
        $item2 = $this->createMock(MenuItemInterface::class);

        $item1
            ->expects($this->once())
            ->method('hideItemExtra');

        $item2
            ->expects($this->once())
            ->method('hideItemExtra');

        $terminal = $this->createMock(Terminal::class);
        $terminal
            ->method('getWidth')
            ->willReturn(100);

        $menu = new CliMenu('Menu', [$item1, $item2], $terminal);

        $confirm = $this->createMock(Confirm::class);
        $confirm
            ->expects($this->once())
            ->method('getStyle')
            ->willReturn(new MenuStyle($terminal));

        $confirm
            ->expects($this->once())
            ->method('display')
            ->with('OK');

        $subMenu = $this->getMockBuilder(CliMenu::class)
            ->setMethods(['confirm'])
            ->setConstructorArgs(['Sub Menu', [], $terminal])
            ->getMock();

        $subMenu->setParent($menu);

        $subMenu
            ->expects($this->once())
            ->method('confirm')
            ->willReturn($confirm);

        $userStateSerializer = $this->createMock(UserStateSerializer::class);
        $userStateSerializer
            ->expects($this->once())
            ->method('serialize')
            ->with($this->isInstanceOf(UserState::class));
        
        $resetProgress = new ResetProgress($userStateSerializer);
        $resetProgress->__invoke($subMenu);
    }
}
