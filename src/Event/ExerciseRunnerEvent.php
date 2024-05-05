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
    public ExecutionContext $context;

    /**
     * @param string $name
     * @param array<mixed> $parameters
     */
    public function __construct(string $name, ExecutionContext $context, array $parameters = [])
    {
        $this->context = $context;
        $parameters['input'] = $context->input;
        $parameters['exercise'] = $context->exercise;
        parent::__construct($name, $parameters);
    }

    /**
     * @return Input
     */
    public function getInput(): Input
    {
        return $this->context->input;
    }

    /**
     * @return ExerciseInterface
     */
    public function getExercise(): ExerciseInterface
    {
        return $this->context->exercise;
    }
}
