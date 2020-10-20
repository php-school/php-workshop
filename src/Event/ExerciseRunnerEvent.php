<?php

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
     * @param array $parameters
     */
    public function __construct($name, ExerciseInterface $exercise, Input $input, array $parameters = [])
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
    public function getInput()
    {
        return $this->input;
    }

    /**
     * @return ExerciseInterface
     */
    public function getExercise()
    {
        return $this->exercise;
    }
}
