<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\ExerciseRunner\Factory;

use PhpSchool\PhpWorkshop\CommandArgument;
use PhpSchool\PhpWorkshop\CommandDefinition;
use PhpSchool\PhpWorkshop\Event\EventDispatcher;
use PhpSchool\PhpWorkshop\Exercise\CliExercise;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\Exercise\ExerciseType;
use PhpSchool\PhpWorkshop\ExerciseRunner\CliRunner;
use PhpSchool\PhpWorkshop\ExerciseRunner\ExerciseRunnerInterface;
use PhpSchool\PhpWorkshop\Process\ProcessFactory;

/**
 * Factory class for `CliRunner`
 */
class CliRunnerFactory implements ExerciseRunnerFactoryInterface
{
    /**
     * @var string
     */
    private static $type = ExerciseType::CLI;

    /**
     * @var EventDispatcher
     */
    private $eventDispatcher;
    
    /**
* @var ProcessFactory 
    */
    private $processFactory;

    /**
     * @param EventDispatcher $eventDispatcher
     */
    public function __construct(EventDispatcher $eventDispatcher, ProcessFactory $processFactory)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->processFactory = $processFactory;
    }

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
        $commandDefinition->addArgument(CommandArgument::required('program'));
    }

    /**
     * Create and return an instance of the runner.
     *
     * @param ExerciseInterface&CliExercise $exercise
     * @return ExerciseRunnerInterface
     */
    public function create(ExerciseInterface $exercise): ExerciseRunnerInterface
    {
        return new CliRunner($exercise, $this->eventDispatcher,  $this->processFactory);
    }
}
