<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Event;

use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\ExerciseRunner\Context\ExecutionContext;
use PhpSchool\PhpWorkshop\Input\Input;

/**
 * An event which is dispatched during exercise running
 */
class ExerciseRunnerEvent extends Event
{
    private ExecutionContext $context;

    /**
     * @param string $name
     * @param array<string, mixed> $parameters
     */
    public function __construct(string $name, ExecutionContext $context, array $parameters = [])
    {
        $this->context = $context;

        $parameters['input'] = $context->getInput();
        $parameters['exercise'] = $context->getExercise();
        parent::__construct($name, $parameters);
    }

    public function getContext(): ExecutionContext
    {
        return $this->context;
    }

    /**
     * @return Input
     */
    public function getInput(): Input
    {
        return $this->context->getInput();
    }

    /**
     * @return ExerciseInterface
     */
    public function getExercise(): ExerciseInterface
    {
        return $this->context->getExercise();
    }
}
