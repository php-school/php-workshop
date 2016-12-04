<?php

namespace PhpSchool\PhpWorkshop\ExerciseRunner\Factory;

use PhpSchool\PhpWorkshop\CommandArgument;
use PhpSchool\PhpWorkshop\CommandDefinition;
use PhpSchool\PhpWorkshop\Event\EventDispatcher;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\Exercise\ExerciseType;
use PhpSchool\PhpWorkshop\ExerciseRunner\CgiRunner;
use PhpSchool\PhpWorkshop\ExerciseRunner\ExerciseRunnerInterface;
use PhpSchool\PhpWorkshop\Utils\RequestRenderer;

/**
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class CgiRunnerFactory implements ExerciseRunnerFactoryInterface
{
    /**
     * @var string
     */
    private static $type = ExerciseType::CGI;

    /**
     * @var EventDispatcher
     */
    private $eventDispatcher;

    /**
     * @var RequestRenderer
     */
    private $requestRenderer;

    /**
     * @param EventDispatcher $eventDispatcher
     */
    public function __construct(EventDispatcher $eventDispatcher, RequestRenderer $requestRenderer)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->requestRenderer = $requestRenderer;
    }

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
        $commandDefinition->addArgument(CommandArgument::required('program'));
    }

    /**
     * Create and return an instance of the runner.
     *
     * @param ExerciseInterface $exercise
     * @return ExerciseRunnerInterface
     */
    public function create(ExerciseInterface $exercise)
    {
        return new CgiRunner($exercise, $this->eventDispatcher, $this->requestRenderer);
    }
}
