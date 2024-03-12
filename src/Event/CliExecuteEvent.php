<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Event;

use PhpSchool\PhpWorkshop\Utils\ArrayObject;
use PhpSchool\PhpWorkshop\Utils\Collection;

/**
 * An event to represent events which occur throughout the verification and running process in
 * `\PhpSchool\PhpWorkshop\ExerciseRunner\CliRunner`.
 */
class CliExecuteEvent extends Event
{
    /**
     * @var Collection<int, string>
     */
    private Collection $args;

    /**
     * @param string $name The event name.
     * @param Collection<int, string> $args The arguments that should be/have been passed to the program.
     * @param array<mixed> $parameters The event parameters.
     */
    public function __construct(string $name, Collection $args, array $parameters = [])
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
    public function prependArg(string $arg): void
    {
        $this->args = $this->args->prepend($arg);
    }

    /**
     * Append an argument to the list of arguments to be passed to the program.
     *
     * @param string $arg
     */
    public function appendArg(string $arg): void
    {
        $this->args = $this->args->append($arg);
    }

    /**
     * Get the arguments to be passed to the program.
     *
     * @return Collection<int, string>
     */
    public function getArgs(): Collection
    {
        return $this->args;
    }
}
