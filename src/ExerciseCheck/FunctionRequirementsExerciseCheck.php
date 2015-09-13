<?php

namespace PhpWorkshop\PhpWorkshop\ExerciseCheck;

/**
 * Interface FunctionRequirementsExerciseCheck
 * @package PhpWorkshop\PhpWorkshop\ExerciseCheck
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
