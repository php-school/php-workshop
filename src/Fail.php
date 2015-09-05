<?php

namespace PhpWorkshop\PhpWorkshop;

use PhpWorkshop\PhpWorkshop\Exercise\ExerciseInterface;

/**
 * Class Fail
 * @package PhpWorkshop\PhpWorkshop
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class Fail
{
    /**
     * @var string
     */
    private $reason;

    /**
     * @var ExerciseInterface
     */
    private $exercise;

    /**
     * @param Exercise\ExerciseInterface $exercise
     * @param $reason
     */
    public function __construct(ExerciseInterface $exercise, $reason)
    {
        $this->exercise = $exercise;
        $this->reason   = $reason;
    }

    /**
     * @return ExerciseInterface
     */
    public function getExercise()
    {
        return $this->exercise;
    }

    /**
     * @return string
     */
    public function getReason()
    {
        return $this->reason;
    }
}