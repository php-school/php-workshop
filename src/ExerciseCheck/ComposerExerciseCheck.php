<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\ExerciseCheck;

/**
 * This interface should be implemented when you require the check `PhpSchool\PhpWorkshop\Check\ComposerCheck` in your
 * exercise.
 */
interface ComposerExerciseCheck
{
    /**
     * Returns an array of composer package names that student's solution should
     * have required via composer.
     *
     * @return array<string> An array of composer package names.
     */
    public function getRequiredPackages(): array;
}
