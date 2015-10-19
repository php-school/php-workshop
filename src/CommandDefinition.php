<?php

namespace PhpSchool\PhpWorkshop;

/**
 * Class CommandDefinition
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
     * @var array
     */
    private $args;

    /**
     * @var string|callable
     */
    private $commandCallable;

    /**
     * CommandDefinition constructor.
     *
     * @param string $name
     * @param array $args
     * @param string|callable $commandCallable
     */
    public function __construct($name, array $args, $commandCallable)
    {
        $this->name             = $name;
        $this->args             = $args;
        $this->commandCallable  = $commandCallable;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return array
     */
    public function getRequiredArgs()
    {
        return $this->args;
    }

    /**
     * @return string|callable
     */
    public function getCommandCallable()
    {
        return $this->commandCallable;
    }
}
