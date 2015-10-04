<?php

namespace PhpWorkshop\PhpWorkshop;

use PhpWorkshop\PhpWorkshop\Exception\CliRouteNotExists;
use PhpWorkshop\PhpWorkshop\Exception\MissingArgumentException;
use Interop\Container\ContainerInterface;
use SebastianBergmann\Environment\Runtime;

/**
 * Class CommandRouter
 * @package PhpWorkshop\PhpWorkshop
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
     * @return mixed
     * @throws CliRouteNotExists
     */
    public function route(array $args = null)
    {
        if (null === $args) {
            $args = isset($_SERVER['argv']) ? $_SERVER['argv'] : [];
        }

        $appName = array_shift($args);

        if (empty($args)) {
            return $this->resolveCallable($this->commands[$this->defaultCommand], $args);
        }

        $commandName = array_shift($args);

        if (!isset($this->commands[$commandName])) {
            throw new CliRouteNotExists($commandName);
        }
        $command = $this->commands[$commandName];
        if (count($args) !== count($command->getRequiredArgs())) {
            $receivedArgs   = count($args);
            $missingArgs    = array_slice($command->getRequiredArgs(), $receivedArgs);
            throw new MissingArgumentException($commandName, $missingArgs);
        }

        return $this->resolveCallable($command, $args);
    }

    /**
     * @param CommandDefinition $command
     * @param array $args
     * @return mixed
     */
    private function resolveCallable(CommandDefinition $command, array $args)
    {
        $commandCallable = $command->getCommandCallable();

        if (is_callable($commandCallable)) {
            return $this->callCommand($commandCallable, $args);
        }

        if (!is_string($commandCallable)) {
            throw new \RuntimeException('Callable must be a callable or a string referencing a callable service');
        }

        if (!$this->container->has($commandCallable)) {
            throw new \RuntimeException('Callable does not exist');
        }

        $commandCallable = $this->container->get($commandCallable);

        if (!is_callable($commandCallable)) {
            throw new \RuntimeException('Service not callable');
        }

        return $this->callCommand($commandCallable, $args);
    }

    /**
     * @param callable $command
     * @param array $arguments
     * @return mixed
     */
    private function callCommand(callable $command, array $arguments)
    {
        return call_user_func_array($command, $arguments);
    }
}
