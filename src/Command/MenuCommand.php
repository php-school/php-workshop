<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Command;

use PhpSchool\CliMenu\CliMenu;

/**
 * A command to open the app menu
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
    public function __invoke(): void
    {
        $this->menu->open();
    }
}
