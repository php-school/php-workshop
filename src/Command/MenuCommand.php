<?php

namespace PhpWorkshop\PhpWorkshop\Command;

use MikeyMike\CliMenu\CliMenu;
use PhpWorkshop\PhpWorkshop\Menu;

/**
 * Class MenuCommand
 * @package PhpWorkshop\PhpWorkshop\Command
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class MenuCommand
{
    /**
     * @var CliMenu
     */
    private $menu;

    /**
     * @param Menu $menu
     */
    public function __construct(Menu $menu)
    {
        $this->menu = $menu;
    }

    /**
     * Run Menu
     */
    public function __invoke()
    {
        $this->menu->display();
    }
}
