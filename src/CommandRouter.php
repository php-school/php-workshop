<?php

namespace PhpSchool\PhpWorkshop;

use PhpSchool\PhpWorkshop\Exception\CliRouteNotExistsException;
use PhpSchool\PhpWorkshop\Exception\MissingArgumentException;
use Interop\Container\ContainerInterface;
use SebastianBergmann\Environment\Runtime;

/**
 * Class CommandRouter
 * @package PhpSchool\PhpWorkshop
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class CommandRouter
{

    /**
     * @var CommandDefinition[]
     */
    private $commands;

    /**
     * @var string
     */
    private $defaultCommand;

    /**
     * @var \Interop\Container\ContainerInterface
     */
    private $container;

    /**
     * @param CommandDefinition[] $commands
     * @param $default
     * @param \Interop\Container\ContainerInterface $container
     */
    public function __construct(array $commands, $default, ContainerInterface $container)
    {
        foreach ($commands as $command) {
            $this->addCommand($command);
        }

        if (!isset($this->commands[$default])) {
            throw new \InvalidArgumentException(sprintf('Default command: "%s" is not available', $default));
        }
        $this->defaultCommand   = $default;
        $this->container        = $container;
    }

    /**
     * @param CommandDefinition $c
     */
    private function addCommand(CommandDefinition $c)
    {
        if (isset($this->commands[$c->getName()])) {
            throw new \InvalidArgumentException(sprintf('Command with name: "%s" already exists', $c->getName()));
        }

        $this->commands[$c->getName()] = $c;
    }

    /**
     * @param array $args
     * @return int
     * @throws CliRouteNotExists
     */
    public function route(array $args = null)
    {
        if (null === $args) {
            $args = isset($_SERVER['argv']) ? $_SERVER['argv'] : [];
        }

        $appName = array_shift($args);

        if (empty($args)) {
            return $this->resolveCallable($this->commands[$this->defaultCommand], array_merge([$appName], $args));
        }

        $commandName = array_shift($args);
        if (!isset($this->commands[$commandName])) {
            $command = $this->findNearestCommand($commandName, $this->commands);
            
            if (false === $command) {
                throw new CliRouteNotExistsException($commandName);
            }
            
            $commandName = $command;
        }
        $command = $this->commands[$commandName];
        if (count($args) !== count($command->getRequiredArgs())) {
            $receivedArgs   = count($args);
            $missingArgs    = array_slice($command->getRequiredArgs(), $receivedArgs);
            throw new MissingArgumentException($commandName, $missingArgs);
        }

        return $this->resolveCallable($command, array_merge([$appName], $args));
    }

    /**
     * Get the closest command to the one typed, but only if there is 3 or less
     * characters different
     *
     * @param string $commandName
     * @param array $commands
     * @return string|false
     */
    private function findNearestCommand($commandName, array $commands)
    {
        $distances = [];
        foreach (array_keys($commands) as $command) {
            $distances[$command] = levenshtein($commandName, $command);
        }
        
        $distances = array_filter(array_unique($distances), function ($distance) {
            return $distance <= 3;
        });
        
        if (empty($distances)) {
            return false;
        }
        
        return array_search(min($distances), $distances);
    }

    /**
     * @param CommandDefinition $command
     * @param array $args
     * @return int
     */
    private function resolveCallable(CommandDefinition $command, array $args)
    {
        $commandCallable = $command->getCommandCallable();

        if (is_callable($commandCallable)) {
            return $this->callCommand($commandCallable, $args);
        }

        if (!is_string($commandCallable)) {
            throw new \RuntimeException('Callable must be a callable or a container entry for a callable service');
        }

        if (!$this->container->has($commandCallable)) {
            throw new \RuntimeException(sprintf('Container has no entry named: "%s"', $commandCallable));
        }

        $callable = $this->container->get($commandCallable);

        if (!is_callable($callable)) {
            throw new \RuntimeException(sprintf('Container entry: "%s" not callable', $commandCallable));
        }

        $return = $this->callCommand($callable, $args);

        if (is_int($return)) {
            return $return;
        }

        return 0;
    }

    /**
     * @param callable $command
     * @param array $arguments
     * @return int
     */
    private function callCommand(callable $command, array $arguments)
    {
        return call_user_func_array($command, $arguments);
    }
}
