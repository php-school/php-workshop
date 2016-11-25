<?php

namespace PhpSchool\PhpWorkshop\ExerciseRunner\Factory;

use PhpSchool\PhpWorkshop\CommandDefinition;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\ExerciseRunner\ExerciseRunnerInterface;

/**
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
interface ExerciseRunnerFactoryInterface
{
    /**
     * Whether the factory supports this exercise type.
     *
     * @param ExerciseInterface $exercise
     * @return bool
     */
    public function supports(ExerciseInterface $exercise);

    /**
     * Add any extra required arguments to the command.
     *
     * @param CommandDefinition $commandDefinition
     */
    public function configureInput(CommandDefinition $commandDefinition);

    /**
     * Create and return an instance of the runner.
     *
     * @param ExerciseInterface $exercise
     * @return ExerciseRunnerInterface
     */
    public function create(ExerciseInterface $exercise);
}
