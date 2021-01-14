<?php

namespace PhpSchool\PhpWorkshopTest\Asset;

use PhpSchool\PhpWorkshop\Exercise\AbstractExercise;
use PhpSchool\PhpWorkshop\Exercise\BaseExerciseTrait;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\Exercise\ExerciseType;

class CliExerciseMissingInterface implements ExerciseInterface
{
    use BaseExerciseTrait;

    /**
     * Get the name of the exercise, like `Hello World!`.
     */
    public function getName(): string
    {
        return 'CLI exercise missing interface';
    }

    /**
     * A short description of the exercise.
     */
    public function getDescription(): string
    {
        return 'CLI exercise missing interface';
    }

    /**
     * Return the type of exercise. This is an ENUM. See `PhpSchool\PhpWorkshop\Exercise\ExerciseType`.
     */
    public function getType(): ExerciseType
    {
        return ExerciseType::CLI();
    }
}
