<?php

namespace PhpSchool\PhpWorkshop\Command;

use PhpSchool\CliMenu\CliMenu;

/**
 * Class MenuCommandInvoker
 * @package PhpSchool\PhpWorkshop\Command
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
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
