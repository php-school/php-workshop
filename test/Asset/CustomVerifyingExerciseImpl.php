<?php

namespace PhpSchool\PhpWorkshopTest\Asset;

use PhpSchool\PhpWorkshop\Exercise\AbstractExercise;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\Exercise\ExerciseType;
use PhpSchool\PhpWorkshop\Exercise\CustomVerifyingExercise;
use PhpSchool\PhpWorkshop\Result\ResultInterface;
use PhpSchool\PhpWorkshop\Result\Success;

class CustomVerifyingExerciseImpl extends AbstractExercise implements ExerciseInterface, CustomVerifyingExercise
{

    /**
     * Get the name of the exercise, like `Hello World!`.
     */
    public function getName(): string
    {
        return 'Custom Verifying exercise';
    }

    /**
     * A short description of the exercise.
     */
    public function getDescription(): string
    {
        return 'Custom Verifying exercise';
    }

    /**
     * Return the type of exercise. This is an ENUM. See `PhpSchool\PhpWorkshop\Exercise\ExerciseType`.
     */
    public function getType(): ExerciseType
    {
        return ExerciseType::CUSTOM();
    }

    public function verify(): ResultInterface
    {
        return new Success('success');
    }
}
