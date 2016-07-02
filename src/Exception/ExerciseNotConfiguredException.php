<?php

namespace PhpSchool\PhpWorkshop\Exception;

use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use RuntimeException;

/**
 * Represents the situation where an exercise requires a check but does not implement
 * the correct interface enforced by the check.
 *
 * @package PhpSchool\PhpWorkshop\Exception
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class ExerciseNotConfiguredException extends RuntimeException
{
    /**
     * Static constructor to create an instance from the exercise and interface name.
     *
     * @param ExerciseInterface $exercise The exercise instance.
     * @param $interface The FQCN of the interface.
     * @return static
     */
    public static function missingImplements(ExerciseInterface $exercise, $interface)
    {
        return new static(sprintf('Exercise: "%s" should implement interface: "%s"', $exercise->getName(), $interface));
    }
}
