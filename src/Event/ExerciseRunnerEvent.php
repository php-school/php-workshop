<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Event;

use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\Input\Input;

/**
 * An event which is dispatched during exercise running
 */
class ExerciseRunnerEvent extends Event
{
    /**
     * @var ExerciseInterface
     */
    private $exercise;

    /**
     * @var Input
     */
    private $input;

    /**
     * @param string $name
     * @param ExerciseInterface $exercise
     * @param Input $input
     * @param array<mixed> $parameters
     */
    public function __construct(string $name, ExerciseInterface $exercise, Input $input, array $parameters = [])
    {
        $parameters['input'] = $input;
        $parameters['exercise'] = $exercise;
        parent::__construct($name, $parameters);

        $this->exercise = $exercise;
        $this->input = $input;
    }

    /**
     * @return Input
     */
    public function getInput(): Input
    {
        return $this->input;
    }

    /**
     * @return ExerciseInterface
     */
    public function getExercise(): ExerciseInterface
    {
        return $this->exercise;
    }
}
