<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Event;

use PhpSchool\PhpWorkshop\Exception\InvalidArgumentException;

/**
 * An event representation.
 */
interface EventInterface
{
    /**
     * Get the name of this event.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Get an array of parameters that were triggered with this event.
     *
     * @return array<mixed>
     */
    public function getParameters(): array;

    /**
     * Get a parameter by it's name.
     *
     * @param string $name The name of the parameter.
     * @return mixed The value.
     * @throws InvalidArgumentException If the parameter by name does not exist.
     */
    public function getParameter(string $name);
}
