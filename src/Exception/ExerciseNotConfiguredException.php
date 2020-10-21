<?php

namespace PhpSchool\PhpWorkshop\Exception;

use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use RuntimeException;

/**
 * Represents the situation where an exercise requires a check but does not implement
 * the correct interface enforced by the check.
 */
class ExerciseNotConfiguredException extends RuntimeException
{
    /**
     * Static constructor to create an instance from the exercise and interface name.
     *
     * @param ExerciseInterface $exercise The exercise instance.
     * @param string $interface The FQCN of the interface.
     * @return self
     */
    public static function missingImplements(ExerciseInterface $exercise, $interface)
    {
        return new self(sprintf('Exercise: "%s" should implement interface: "%s"', $exercise->getName(), $interface));
    }
}
