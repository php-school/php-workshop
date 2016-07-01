<?php

namespace PhpSchool\PhpWorkshop\Exception;

use PhpSchool\PhpWorkshop\Check\CheckInterface;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use RuntimeException;

/**
 * Represents the situation when a workshop developer tries to use a check in an exercise which has
 * a type not supported by the check.
 *
 * @package PhpSchool\PhpWorkshop\Exception
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class CheckNotApplicableException extends RuntimeException
{
    /**
     * Static constructor to create an instance from the check & exercise.
     *
     * @param CheckInterface $check The check Instance.
     * @param ExerciseInterface $exercise The exercise Instance.
     * @return static
     */
    public static function fromCheckAndExercise(CheckInterface $check, ExerciseInterface $exercise)
    {
        return new static(
            sprintf(
                'Check: "%s" cannot process exercise: "%s" with type: "%s"',
                $check->getName(),
                $exercise->getName(),
                $exercise->getType()
            )
        );
    }
}
