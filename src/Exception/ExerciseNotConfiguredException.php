<?php

namespace PhpSchool\PhpWorkshop\Exception;

use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use RuntimeException;

/**
 * Class ExerciseNotConfiguredException
 * @package PhpSchool\PhpWorkshop\Exception
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class ExerciseNotConfiguredException extends RuntimeException
{
    /**
     * @param ExerciseInterface $exercise
     * @param $interface
     * @return static
     */
    public static function missingImplements(ExerciseInterface $exercise, $interface)
    {
        return new static(sprintf('Exercise: "%s" should implement interface: "%s"', $exercise->getName(), $interface));
    }
}
