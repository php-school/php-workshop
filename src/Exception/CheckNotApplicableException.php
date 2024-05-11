<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Exception;

use PhpSchool\PhpWorkshop\Check\CheckInterface;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use RuntimeException;

/**
 * Represents the situation when a workshop developer tries to use a check in an exercise which has
 * a type not supported by the check.
 */
class CheckNotApplicableException extends RuntimeException
{
    /**
     * Static constructor to create an instance from the check & exercise.
     *
     * @param CheckInterface $check The check Instance.
     * @param ExerciseInterface $exercise The exercise Instance.
     * @return self
     */
    public static function fromCheckAndExercise(CheckInterface $check, ExerciseInterface $exercise): self
    {
        return new self(
            sprintf(
                'Check: "%s" cannot process exercise: "%s" with type: "%s"',
                $check->getName(),
                $exercise->getName(),
                $exercise->getType(),
            ),
        );
    }
}
