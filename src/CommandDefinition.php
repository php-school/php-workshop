<?php

namespace PhpWorkshop\PhpWorkshop;

/**
 * Class CommandDefinition
 * @package PhpWorkshop\PhpWorkshop
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */

class CommandDefinition
{
    private $name;
    private $args;
    private $commandCallable;

    public function __construct($name, $args, $commandCallable)
    {
        $this->name = $name;
        $this->args = $args;
        $this->commandCallable = $commandCallable;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getRequiredArgs()
    {
        return $this->args;
    }

    public function getCommandCallable()
    {
        return $this->commandCallable;
    }
}
