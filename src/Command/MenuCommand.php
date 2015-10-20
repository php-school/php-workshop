<?php

namespace PhpSchool\PhpWorkshop\Command;

use PhpSchool\CliMenu\CliMenu;

/**
 * Class MenuCommand
 * @package PhpSchool\PhpWorkshop\Command
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
     * @return void
     */
    public function __invoke()
    {
        $this->menu->open();
    }
}
