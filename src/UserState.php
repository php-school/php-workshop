<?php

namespace PhpSchool\PhpWorkshop;

/**
 * Class UserState
 * @package PhpSchool\PhpWorkshop
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
        if (!in_array($exercise, $this->completedExercises)) {
            $this->completedExercises[] = $exercise;
        }
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

    /**
     * @param string $exercise
     * @return bool
     */
    public function completedExercise($exercise)
    {
        return in_array($exercise, $this->completedExercises);
    }
}
