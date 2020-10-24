<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Input;

use PhpSchool\PhpWorkshop\CommandDefinition;
use PhpSchool\PhpWorkshop\Exception\InvalidArgumentException;

/**
 * Container for command arguments
 */
class Input
{
    /**
     * @var string
     */
    private $appName;

    /**
     * @var array<string, string|null>
     */
    private $arguments = [];

    /**
     * @param string $appName
     * @param array<string, string|null> $arguments
     */
    public function __construct(string $appName, array $arguments = [])
    {
        $this->appName = $appName;
        $this->arguments = $arguments;
    }

    /**
     * @return string
     */
    public function getAppName(): string
    {
        return $this->appName;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasArgument(string $name): bool
    {
        return array_key_exists($name, $this->arguments);
    }

    /**
     * @param string $name
     * @return ?string
     * @throws InvalidArgumentException
     */
    public function getArgument(string $name): ?string
    {
        if (!$this->hasArgument($name)) {
            throw new InvalidArgumentException(sprintf('Argument with name: "%s" does not exist', $name));
        }

        return $this->arguments[$name];
    }

    /**
     * @param string $name
     * @return string
     * @throws InvalidArgumentException
     */
    public function getRequiredArgument(string $name): string
    {
        $arg = $this->getArgument($name);

        if (null === $arg) {
            throw new InvalidArgumentException(sprintf('Argument with name: "%s" is required', $name));
        }

        return $arg;
    }

    /**
     * @param string $name
     * @param string $value
     */
    public function setArgument(string $name, string $value): void
    {
        $this->arguments[$name] = $value;
    }
}
