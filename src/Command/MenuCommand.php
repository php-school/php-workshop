<?php

namespace PhpWorkshop\PhpWorkshop\Command;

use MikeyMike\CliMenu\CliMenu;

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
     * @param CliMenu $menu
     */
    public function __construct(CliMenu $menu)
    {
        $this->menu = $menu;
    }

    /**
     * Run Menu
     */
    public function __invoke()
    {
        return $this->menu->display();
    }
}
