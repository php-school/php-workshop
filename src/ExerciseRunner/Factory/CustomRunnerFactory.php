<?php

namespace PhpSchool\PhpWorkshop\ExerciseRunner\Factory;

use PhpSchool\PhpWorkshop\CommandArgument;
use PhpSchool\PhpWorkshop\CommandDefinition;
use PhpSchool\PhpWorkshop\Event\EventDispatcher;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\Exercise\ExerciseType;
use PhpSchool\PhpWorkshop\ExerciseRunner\CgiRunner;
use PhpSchool\PhpWorkshop\ExerciseRunner\CustomRunner;
use PhpSchool\PhpWorkshop\ExerciseRunner\ExerciseRunnerInterface;

/**
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class CustomRunnerFactory implements ExerciseRunnerFactoryInterface
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
    public function supports(ExerciseInterface $exercise)
    {
        return $exercise->getType()->getValue() === self::$type;
    }

    /**
     * Add any extra required arguments to the command.
     *
     * @param CommandDefinition $commandDefinition
     */
    public function configureInput(CommandDefinition $commandDefinition)
    {
    }

    /**
     * Create and return an instance of the runner.
     *
     * @param ExerciseInterface $exercise
     * @return ExerciseRunnerInterface
     */
    public function create(ExerciseInterface $exercise)
    {
        return new CustomRunner($exercise);
    }
}
