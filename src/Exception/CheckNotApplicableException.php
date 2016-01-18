<?php

namespace PhpSchool\PhpWorkshop\Exception;

use PhpSchool\PhpWorkshop\Check\CheckInterface;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use RuntimeException;

/**
 * Class CheckNotApplicableException
 * @package PhpSchool\PhpWorkshop\Exception
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class CheckNotApplicableException extends RuntimeException
{
    /**
     * @param CheckInterface $check
     * @param ExerciseInterface $exercise
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
