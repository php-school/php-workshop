<?php

namespace PhpWorkshop\PhpWorkshop;

/**
 * Class UserState
 * @package PhpWorkshop\PhpWorkshop
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class UserState
{

    /**
     * @var string
     */
    private $currentExercise;

    /**
     * @var array
     */
    private $completedExercises;

    /**
     * @param string $currentExercise
     * @param array $completedExercises
     */
    public function __construct(array $completedExercises = [], $currentExercise = null)
    {
        $this->currentExercise = $currentExercise;
        $this->completedExercises = $completedExercises;
    }

    /**
     * @param string $exercise
     */
    public function addCompletedExercise($exercise)
    {
        $this->completedExercises[] = $exercise;
    }

    /**
     * @param string $exercise
     */
    public function setCurrentExercise($exercise)
    {
        $this->currentExercise = $exercise;
    }

    /**
     * @return string
     */
    public function getCurrentExercise()
    {
        return $this->currentExercise;
    }

    /**
     * @return array
     */
    public function getCompletedExercises()
    {
        return $this->completedExercises;
    }

    /**
     * @return bool
     */
    public function isAssignedExercise()
    {
        return null !== $this->currentExercise;
    }
}
