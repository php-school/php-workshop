<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Event;

use PhpSchool\PhpWorkshop\Exception\InvalidArgumentException;

/**
 * A generic `PhpSchool\PhpWorkshop\Event\EventInterface` implementation.
 */
class Event implements EventInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var array<mixed>
     */
    protected $parameters;

    /**
     * @param string $name The event name.
     * @param array<mixed> $parameters The event parameters.
     */
    public function __construct(string $name, array $parameters = [])
    {
        $this->name = $name;
        $this->parameters = $parameters;
    }

    /**
     * Get the name of this event.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get an array of parameters that were triggered with this event.
     *
     * @return array<mixed>
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * Get a parameter by its name.
     *
     * @param string $name The name of the parameter.
     * @return mixed The value.
     * @throws InvalidArgumentException If the parameter by name does not exist.
     */
    public function getParameter(string $name): mixed
    {
        if (!array_key_exists($name, $this->parameters)) {
            throw new InvalidArgumentException(sprintf('Parameter: "%s" does not exist', $name));
        }

        return $this->parameters[$name];
    }
}
