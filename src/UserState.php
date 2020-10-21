<?php

namespace PhpSchool\PhpWorkshop;

/**
 * This class represents the current state of the user. Which exercises she/he has completed and
 * which is the current exercise being attempted.
 */
class UserState
{

    /**
     * @var string
     */
    private $currentExercise;

    /**
     * @var array<string>
     */
    private $completedExercises;

    /**
     * Take an array of completed exercises (the exercise names) and a string containing the current
     * exercise.
     *
     * @param array<string> $completedExercises An array of exercise names.
     * @param string|null $currentExercise Can be null in-case the student did not start an exercise yet.
     */
    public function __construct(array $completedExercises = [], string $currentExercise = null)
    {
        $this->currentExercise = $currentExercise;
        $this->completedExercises = $completedExercises;
    }

    /**
     * Mark an exercise as completed. Should be the exercise name.
     *
     * @param string $exercise
     */
    public function addCompletedExercise(string $exercise): void
    {
        if (!in_array($exercise, $this->completedExercises, true)) {
            $this->completedExercises[] = $exercise;
        }
    }

    /**
     * Set the current exercise. Should be the exercise name.
     *
     * @param string $exercise
     */
    public function setCurrentExercise(string $exercise): void
    {
        $this->currentExercise = $exercise;
    }

    /**
     * Get the current exercise name.
     *
     * @return string
     */
    public function getCurrentExercise(): string
    {
        return $this->currentExercise;
    }

    /**
     * Get an array of the completed exercises (the exercise names).
     *
     * @return array<string>
     */
    public function getCompletedExercises(): array
    {
        return $this->completedExercises;
    }

    /**
     * Check whether the student is actually assigned an exercise.
     *
     * @return bool
     */
    public function isAssignedExercise(): bool
    {
        return null !== $this->currentExercise;
    }

    /**
     * Check whether the student has completed an exercise by the exercise name.
     *
     * @param string $exercise
     * @return bool
     */
    public function completedExercise(string $exercise): bool
    {
        return in_array($exercise, $this->completedExercises, true);
    }
}
