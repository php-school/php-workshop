<?php

namespace PhpSchool\PhpWorkshop\Exercise;

/**
 * This interface describes the additional methods a CLI type exercise should implement.
 *
 * @package PhpSchool\PhpWorkshop\Exercise
 */
interface CliExercise extends ProvidesSolution
{
    /**
     * This method should return an array of strings which will be passed to the student's solution
     * as command line arguments.
     *
     * @return string[] An array of string arguments.
     */
    public function getArgs();
}
