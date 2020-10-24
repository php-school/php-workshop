<?php

namespace PhpSchool\PhpWorkshop;

use PhpSchool\PhpWorkshop\Exception\InvalidArgumentException;

/**
 * Represents a command in the workshop framework. Simply consists of a
 * command name, required arguments and either a service name or callable to
 * execute when the command is run.
 */
class CommandDefinition
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var array<CommandArgument>
     */
    private $args = [];

    /**
     * @var string|callable
     */
    private $commandCallable;

    /**
     * @param string $name The name of the command (this is how the student would invoke the command from the cli)
     * @param string[]|CommandArgument[] $args A list of arguments. Must be an array of strings or `CommandArgument`'s.
     * @param string|callable $commandCallable The name of a callable container entry or an actual PHP callable.
     */
    public function __construct(string $name, array $args, $commandCallable)
    {
        $this->name = $name;
        $this->commandCallable = $commandCallable;

        array_walk($args, function ($arg) {
            $this->addArgument($arg);
        });
    }

    /**
     * @param string|CommandArgument $argument
     * @return $this
     */
    public function addArgument($argument): self
    {
        if (!is_string($argument) && !$argument instanceof CommandArgument) {
            throw InvalidArgumentException::notValidParameter(
                'argument',
                ['string', CommandArgument::class],
                $argument
            );
        }

        if (is_string($argument)) {
            $argument = new CommandArgument($argument);
        }

        if (count($this->args) === 0) {
            $this->args[] = $argument;
            return $this;
        }

        $previousArgument = end($this->args);
        if ($previousArgument->isOptional() && $argument->isRequired()) {
            throw new InvalidArgumentException(sprintf(
                'A required argument cannot follow an optional argument'
            ));
        }

        $this->args[] = $argument;
        return $this;
    }

    /**
     * Get the name of the command.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get the list of required arguments.
     *
     * @return array<CommandArgument>
     */
    public function getRequiredArgs(): array
    {
        return $this->args;
    }

    /**
     * Get the callable associated with this command.
     *
     * @return string|callable
     */
    public function getCommandCallable()
    {
        return $this->commandCallable;
    }
}
