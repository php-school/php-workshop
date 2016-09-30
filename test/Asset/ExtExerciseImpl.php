<?php

namespace PhpSchool\PhpWorkshopTest\Asset;

use PhpSchool\PhpWorkshop\Exercise\AbstractExercise;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\Exercise\ExerciseType;
use PhpSchool\PhpWorkshop\Exercise\ExtExercise;
use PhpSchool\PhpWorkshop\Result\ResultInterface;
use PhpSchool\PhpWorkshop\Result\Success;

/**
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class ExtExerciseImpl extends AbstractExercise implements ExerciseInterface, ExtExercise
{

    /**
     * Get the name of the exercise, like `Hello World!`.
     *
     * @return string
     */
    public function getName()
    {
        return 'EXT exercise';
    }

    /**
     * A short description of the exercise.
     *
     * @return string
     */
    public function getDescription()
    {
        return 'EXT exercise';
    }

    /**
     * Return the type of exercise. This is an ENUM. See `PhpSchool\PhpWorkshop\Exercise\ExerciseType`.
     *
     * @return ExerciseType
     */
    public function getType()
    {
        return ExerciseType::EXT();
    }

    /**
     * @return ResultInterface
     */
    public function verify()
    {
        return new Success('success');
    }
}
