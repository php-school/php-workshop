<?php

namespace PhpSchool\PhpWorkshop\Exercise;

use PhpSchool\PhpWorkshop\Solution\SolutionInterface;

/**
 * This interface describes the additional methods a CLI type exercise should implement.
 *
 * @package PhpSchool\PhpWorkshop\Exercise
 */
interface CliExercise
{
    /**
     * Get the exercise solution.
     *
     * @return SolutionInterface
     */
    public function getSolution();

    /**
     * Get the absolute path to the markdown file which contains the exercise problem.
     *
     * @return string
     */
    public function getProblem();

    /**
     * This method should return an array of strings which will be passed to the student's solution
     * as command line arguments.
     *
     * @return string[] An array of string arguments.
     */
    public function getArgs();
}
