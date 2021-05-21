<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop;

use InvalidArgumentException;
use PhpSchool\PhpWorkshop\Event\EventDispatcher;
use PhpSchool\PhpWorkshop\Exception\CliRouteNotExistsException;
use PhpSchool\PhpWorkshop\Exception\MissingArgumentException;
use Psr\Container\ContainerInterface;
use PhpSchool\PhpWorkshop\Input\Input;
use RuntimeException;

/**
 * Parses $argv (or passed array) and attempts to find a command
 * which is suitable for what was typed on the cli. It then executes the callable
 * associated with that command definition.
 */
class CommandRouter
{
    /**
     * @var array<string, CommandDefinition>
     */
    private $commands;

    /**
     * @var string
     */
    private $defaultCommand;

    /**
     * @var EventDispatcher
     */
    private $eventDispatcher;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * Accepts an array of `CommandDefinition`'s which represent the application. Also takes a default
     * (name of one of the commands) which will be used if the workshop was invoked with no arguments.
     *
     * Also accepts an instance of the container so it can look for services in there which may by defined
     * as the callable for one of the command definitions.
     *
     * @param array<CommandDefinition> $commands An array of command definitions
     * @param string $default The default command to use (if the workshop was invoked with no arguments)
     * @param EventDispatcher $eventDispatcher
     * @param ContainerInterface $container An instance of the container
     */
    public function __construct(
        array $commands,
        string $default,
        EventDispatcher $eventDispatcher,
        ContainerInterface $container
    ) {
        foreach ($commands as $command) {
            $this->addCommand($command);
        }

        if (!isset($this->commands[$default])) {
            throw new InvalidArgumentException(sprintf('Default command: "%s" is not available', $default));
        }
        $this->defaultCommand = $default;
        $this->eventDispatcher = $eventDispatcher;
        $this->container = $container;
    }

    /**
     * @param CommandDefinition $c
     */
    public function addCommand(CommandDefinition $c): void
    {
        if (isset($this->commands[$c->getName()])) {
            throw new InvalidArgumentException(sprintf('Command with name: "%s" already exists', $c->getName()));
        }

        $this->commands[$c->getName()] = $c;
    }

    /**
     * Attempts to route the command. Parses `$argv` (or a given array), extracting the command name and
     * arguments. Using the command name, the command definition is looked up.
     *
     * The number of arguments are validated against the required arguments for the command
     * (specified by the definition)
     *
     * We get the callable from the command definition, or if it is the name of a service, we lookup the service
     * in the container and validate that it is a callable.
     *
     * Finally, the callable is invoked with the arguments passed from the cli. The return value of
     * callable is returned (if it is an integer, if not zero (success) is returned).
     *
     * @param array<string>|null $args
     * @return int
     * @throws CliRouteNotExistsException
     */
    public function route(array $args = null): int
    {
        if (null === $args) {
            $args = $_SERVER['argv'] ?? [];
        }

        $appName = array_shift($args);

        if (empty($args)) {
            return $this->resolveCallable($this->commands[$this->defaultCommand], new Input($appName));
        }

        $commandName = array_shift($args);
        if (!isset($this->commands[$commandName])) {
            $command = $this->findNearestCommand($commandName, $this->commands);

            if (null === $command) {
                throw new CliRouteNotExistsException($commandName);
            }

            $commandName = $command;
        }
        $command = $this->commands[$commandName];

        $this->eventDispatcher->dispatch(new Event\Event('route.pre.resolve.args', ['command' => $command]));

        $input = $this->parseArgs($commandName, $command->getRequiredArgs(), $appName, $args);

        return $this->resolveCallable($command, $input);
    }

    /**
     * @param string $commandName
     * @param CommandArgument[] $definitionArgs
     * @param string $appName
     * @param array<string> $givenArgs
     * @return Input
     */
    private function parseArgs(string $commandName, array $definitionArgs, string $appName, array $givenArgs): Input
    {
        $parsedArgs = [];

        while (null !== ($definitionArg = array_shift($definitionArgs))) {
            $arg = array_shift($givenArgs);

            if (null === $arg && !$definitionArg->isOptional()) {
                throw new MissingArgumentException($commandName, array_map(function (CommandArgument $argument) {
                    return $argument->getName();
                }, array_merge([$definitionArg], $definitionArgs)));
            }

            $parsedArgs[$definitionArg->getName()] = $arg;
        }

        return new Input($appName, $parsedArgs);
    }

    /**
     * Get the closest command to the one typed, but only if there is 3 or less
     * characters different
     *
     * @param string $commandName
     * @param array<string, CommandDefinition> $commands
     * @return string|null
     */
    private function findNearestCommand(string $commandName, array $commands): ?string
    {
        $distances = [];
        foreach (array_keys($commands) as $command) {
            $distances[$command] = levenshtein($commandName, $command);
        }

        $distances = array_filter(array_unique($distances), function ($distance) {
            return $distance <= 3;
        });

        if (empty($distances)) {
            return null;
        }

        $name = array_search(min($distances), $distances, true);
        return is_string($name) ? $name : null;
    }

    /**
     * @param CommandDefinition $command
     * @param Input $input
     * @return int
     */
    private function resolveCallable(CommandDefinition $command, Input $input): int
    {
        $commandCallable = $command->getCommandCallable();

        if (is_callable($commandCallable)) {
            $return = $this->callCommand($command, $commandCallable, $input);
            return is_int($return) ? $return : 0;
        }

        if (!is_string($commandCallable)) {
            throw new RuntimeException('Callable must be a callable or a container entry for a callable service');
        }

        if (!$this->container->has($commandCallable)) {
            throw new RuntimeException(sprintf('Container has no entry named: "%s"', $commandCallable));
        }

        $callable = $this->container->get($commandCallable);

        if (!is_callable($callable)) {
            throw new RuntimeException(sprintf('Container entry: "%s" not callable', $commandCallable));
        }

        $return = $this->callCommand($command, $callable, $input);

        return is_int($return) ? $return : 0;
    }

    /**
     * @param CommandDefinition $command
     * @param callable $callable
     * @param Input $input
     * @return ?int
     */
    private function callCommand(CommandDefinition $command, callable $callable, Input $input): ?int
    {
        $this->eventDispatcher->dispatch(new Event\Event('route.pre.invoke'));
        $this->eventDispatcher->dispatch(new Event\Event(sprintf('route.pre.invoke.%s', $command->getName())));

        return $callable($input);
    }
}
