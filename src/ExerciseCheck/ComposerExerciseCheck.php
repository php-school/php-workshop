<?php

namespace PhpSchool\PhpWorkshop\ExerciseCheck;

/**
 * Interface ComposerExerciseCheck
 * @package PhpSchool\PhpWorkshop\ExerciseCheck
 */
interface ComposerExerciseCheck
{
    /**
     * @return array
     */
    public function getRequiredPackages();
}
