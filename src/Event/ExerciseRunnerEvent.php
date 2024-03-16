<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Event;

use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\ExerciseRunner\Context\RunnerContext;
use PhpSchool\PhpWorkshop\ExerciseRunner\Environment;
use PhpSchool\PhpWorkshop\Input\Input;

/**
 * An event which is dispatched during exercise running
 */
class ExerciseRunnerEvent extends Event
{
    /**
     * @param string $name
     * @param array<mixed> $parameters
     */
    public function __construct(string $name, public RunnerContext $context, array $parameters = [])
    {
        $parameters['input'] = $context->getExecutionContext()->input;
        $parameters['exercise'] = $context->getExecutionContext()->exercise;
        parent::__construct($name, $context, $parameters);
    }

    /**
     * @return Input
     */
    public function getInput(): Input
    {
        return $this->context->getExecutionContext()->input;
    }

    /**
     * @return ExerciseInterface
     */
    public function getExercise(): ExerciseInterface
    {
        return $this->context->getExecutionContext()->exercise;
    }
}
