<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\ExerciseRunner;

use PhpSchool\PhpWorkshop\CommandDefinition;
use PhpSchool\PhpWorkshop\Exception\InvalidArgumentException;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\ExerciseRunner\Context\ExecutionContext;
use PhpSchool\PhpWorkshop\ExerciseRunner\Context\RunnerContext;
use PhpSchool\PhpWorkshop\ExerciseRunner\Factory\ExerciseRunnerFactoryInterface;

/**
 * Allow factories to configure input and fetch the correct runner for the exercise
 */
class RunnerManager
{
    /**
     * @var array<ExerciseRunnerFactoryInterface>
     */
    private $factories = [];

    /**
     * @param ExerciseRunnerFactoryInterface $factory
     */
    public function addFactory(ExerciseRunnerFactoryInterface $factory): void
    {
        $this->factories[] = $factory;
    }

    /**
     * @param ExerciseInterface $exercise
     * @param CommandDefinition $commandDefinition
     */
    public function configureInput(ExerciseInterface $exercise, CommandDefinition $commandDefinition): void
    {
        $this->getFactory($exercise)->configureInput($commandDefinition);
    }

    /**
     * @param ExerciseInterface $exercise
     * @return ExerciseRunnerInterface
     */
    public function getRunner(ExerciseInterface $exercise): ExerciseRunnerInterface
    {
        return $this->getFactory($exercise)->create($exercise);
    }

    public function wrapContext(ExecutionContext $executionContext): RunnerContext
    {
        return $this->getFactory($executionContext->exercise)->wrapContext($executionContext);
    }

    /**
     * @param ExerciseInterface $exercise
     * @return ExerciseRunnerFactoryInterface
     * @throws InvalidArgumentException
     */
    private function getFactory(ExerciseInterface $exercise): ExerciseRunnerFactoryInterface
    {
        foreach ($this->factories as $factory) {
            if ($factory->supports($exercise)) {
                return $factory;
            }
        }

        throw new InvalidArgumentException(
            sprintf('Exercise Type: "%s" not supported', $exercise->getType()->getValue())
        );
    }
}
