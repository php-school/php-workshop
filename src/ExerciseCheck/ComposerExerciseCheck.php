<?php

namespace PhpSchool\PhpWorkshop\ExerciseCheck;

/**
 * This interface should be implemented when you require the check `PhpSchool\PhpWorkshop\Check\ComposerCheck` in your
 * exercise.
 *
 * @package PhpSchool\PhpWorkshop\ExerciseCheck
 */
interface ComposerExerciseCheck
{
    /**
     * Returns an array of composer package names that student's solution should
     * have required via composer.
     *
     * @return array An array of composer package names.
     */
    public function getRequiredPackages();
}
