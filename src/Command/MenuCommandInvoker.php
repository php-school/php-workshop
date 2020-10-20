<?php

namespace PhpSchool\PhpWorkshop\Command;

use PhpSchool\CliMenu\CliMenu;

class MenuCommandInvoker
{

    /**
     * @var callable
     */
    private $command;

    /**
     * @param callable $command
     */
    public function __construct(callable $command)
    {
        $this->command = $command;
    }

    /**
     * @param CliMenu $menu
     */
    public function __invoke(CliMenu $menu)
    {
        $menu->close();
        $command = $this->command;
        $command();
    }
}
