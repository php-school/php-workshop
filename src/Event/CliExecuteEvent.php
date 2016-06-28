<?php

namespace PhpSchool\PhpWorkshop\Event;

use Assert\Assertion;
use PhpSchool\PhpWorkshop\Utils\ArrayObject;

/**
 * An event to represent events which occur throughout the verification and running process in
 * `\PhpSchool\PhpWorkshop\ExerciseRunner\CliRunner`
 *
 * @package PhpSchool\PhpWorkshop\Event
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class CliExecuteEvent extends Event
{
    /**
     * @var ArrayObject
     */
    private $args;

    /**
     * @param string $name The event name.
     * @param ArrayObject $args The arguments that should be/have been passed to the program.
     * @param array $parameters The event parameters.
     */
    public function __construct($name, ArrayObject $args, array $parameters = [])
    {
        $parameters['args'] = $args;
        parent::__construct($name, $parameters);
        $this->args = $args;
    }

    /**
     * Prepend an argument to the list of arguments to be passed to the program.
     *
     * @param string $arg
     */
    public function prependArg($arg)
    {
        Assertion::string($arg);
        $this->args = $this->args->prepend($arg);
    }

    /**
     * Append an argument to the list of arguments to be passed to the program.
     *
     * @param string $arg
     */
    public function appendArg($arg)
    {
        Assertion::string($arg);
        $this->args = $this->args->append($arg);
    }

    /**
     * Get the arguments to be passed to the program.
     *
     * @return ArrayObject
     */
    public function getArgs()
    {
        return $this->args;
    }
}
