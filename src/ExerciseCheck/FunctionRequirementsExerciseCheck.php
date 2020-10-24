<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\ExerciseCheck;

/**
 * This interface should be implemented when you require the check
 * `PhpSchool\PhpWorkshop\Check\FunctionRequirementsCheck` in your exercise.
 */
interface FunctionRequirementsExerciseCheck
{
    /**
     * Returns an array of function names that the student's solution should use. The solution
     * will be parsed and checked for usages of these functions.
     *
     * @return array<string> An array of function names that *should* be used.
     */
    public function getRequiredFunctions(): array;

    /**
     * Returns an array of function names that the student's solution should not use. The solution
     * will be parsed and checked for usages of these functions.
     *
     * @return array<string> An array of function names that *should not* be used.
     */
    public function getBannedFunctions(): array;
}
