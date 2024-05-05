<?php

namespace PhpSchool\PhpWorkshop\Exercise;

use PhpSchool\PhpWorkshop\ExerciseDispatcher;

class MockExercise extends AbstractExercise implements ExerciseInterface
{
    public function getName(): string
    {
        return 'Mock Exercise';
    }

    public function getDescription(): string
    {
        return 'Mock Exercise';
    }

    public function getType(): ExerciseType
    {
        return ExerciseType::CUSTOM();
    }

    public function getProblem(): string
    {
        return 'problem-file.md';
    }
}
