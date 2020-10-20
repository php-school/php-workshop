<?php

namespace PhpSchool\PhpWorkshop\Exercise;

use PhpSchool\PhpWorkshop\Solution\SolutionInterface;

/**
 * Exercises can implement this method if they provide solutions
 */
interface ProvidesSolution
{
    /**
     * Get the exercise solution.
     *
     * @return SolutionInterface
     */
    public function getSolution();
}
