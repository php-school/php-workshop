<?php

namespace PhpSchool\PhpWorkshop\ExerciseRunner\Factory;

use PhpSchool\PhpWorkshop\CommandDefinition;
use PhpSchool\PhpWorkshop\Exercise\CustomVerifyingExercise;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\Exercise\ExerciseType;
use PhpSchool\PhpWorkshop\ExerciseRunner\CustomVerifyingRunner;
use PhpSchool\PhpWorkshop\ExerciseRunner\ExerciseRunnerInterface;

/**
 * Factory class for `CustomVerifyingRunner`
 */
class CustomVerifyingRunnerFactory implements ExerciseRunnerFactoryInterface
{
    /**
     * @var string
     */
    private static $type = ExerciseType::CUSTOM;

    /**
     * Whether the factory supports this exercise type.
     *
     * @param ExerciseInterface $exercise
     * @return bool
     */
    public function supports(ExerciseInterface $exercise): bool
    {
        return $exercise->getType()->getValue() === self::$type;
    }

    /**
     * Add any extra required arguments to the command.
     *
     * @param CommandDefinition $commandDefinition
     */
    public function configureInput(CommandDefinition $commandDefinition): void
    {
    }

    /**
     * Create and return an instance of the runner.
     *
     * @param ExerciseInterface&CustomVerifyingExercise $exercise
     * @return ExerciseRunnerInterface
     */
    public function create(ExerciseInterface $exercise): ExerciseRunnerInterface
    {
        return new CustomVerifyingRunner($exercise);
    }
}
