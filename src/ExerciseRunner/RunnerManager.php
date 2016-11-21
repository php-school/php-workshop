<?php

namespace PhpSchool\PhpWorkshop\ExerciseRunner;

use Interop\Container\ContainerInterface;
use PhpSchool\PhpWorkshop\CommandDefinition;
use PhpSchool\PhpWorkshop\Exception\InvalidArgumentException;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\ExerciseRunner\Factory\ExerciseRunnerFactoryInterface;

/**
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class RunnerManager
{
    /**
     * @var ExerciseRunnerFactoryInterface[]
     */
    private $factories = [];

    /**
     * @param ExerciseRunnerFactoryInterface $factory
     */
    public function addFactory(ExerciseRunnerFactoryInterface $factory)
    {
        $this->factories[] = $factory;
    }

    /**
     * @param ExerciseInterface $exercise
     * @param CommandDefinition $commandDefinition
     */
    public function configureInput(ExerciseInterface $exercise, CommandDefinition $commandDefinition)
    {
        $this->getFactory($exercise)->configureInput($commandDefinition);
    }

    /**
     * @param ExerciseInterface $exercise
     * @return ExerciseRunnerInterface
     */
    public function getRunner(ExerciseInterface $exercise)
    {
        return $this->getFactory($exercise)->create($exercise);
    }

    /**
     * @param ExerciseInterface $exercise
     * @return ExerciseRunnerFactoryInterface
     * @throws InvalidArgumentException
     */
    private function getFactory(ExerciseInterface $exercise)
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
