<?php

namespace PhpSchool\PhpWorkshop\ExerciseCheck;

/**
 * Interface FunctionRequirementsExerciseCheck
 * @package PhpSchool\PhpWorkshop\ExerciseCheck
 */
interface FunctionRequirementsExerciseCheck
{
    /**
     * @return string[]
     */
    public function getRequiredFunctions();

    /**
     * @return string[]
     */
    public function getBannedFunctions();
}
