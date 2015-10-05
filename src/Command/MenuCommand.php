<?php

namespace PhpWorkshop\PhpWorkshop\Command;

use PhpWorkshop\PhpWorkshop\Menu;

/**
 * Class MenuCommand
 * @package PhpWorkshop\PhpWorkshop\Command
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class MenuCommand
{
    /**
     * @var Menu
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
     * @return void
     */
    public function __invoke()
    {
        $this->menu->display();
    }
}
