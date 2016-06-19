<?php

namespace PhpSchool\PhpWorkshop;

/**
 * Represents a command in the workshop framework. Simply consists of a
 * command name, required arguments and either a service name of callable to
 * execute when the command is run.
 *
 * @package PhpSchool\PhpWorkshop
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class CommandDefinition
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string[]
     */
    private $args;

    /**
     * @var string|callable
     */
    private $commandCallable;

    /**
     * @param string $name The name of the command (this is how the student would invoke the command from the cli)
     * @param string[] $args A list of required arguments. This must be an array of strings.
     * @param string|callable $commandCallable The name of a callable container entry or an actual PHP callable.
     */
    public function __construct($name, array $args, $commandCallable)
    {
        $this->name             = $name;
        $this->args             = $args;
        $this->commandCallable  = $commandCallable;
    }

    /**
     * Get the name of the command
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get the list of required arguments
     *
     * @return array
     */
    public function getRequiredArgs()
    {
        return $this->args;
    }

    /**
     * Get the callable associated with this command
     *
     * @return string|callable
     */
    public function getCommandCallable()
    {
        return $this->commandCallable;
    }
}
