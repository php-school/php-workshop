<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Exercise;

use PhpSchool\PhpWorkshop\Solution\SolutionInterface;

/**
 * Exercises can implement this method if they want to provide some
 * code for the user to start with, eg a failing solution.
 */
interface ProvidesInitialCode
{
    /**
     * Get the exercise solution.
     *
     * @return SolutionInterface
     */
    public function getInitialCode(): SolutionInterface;
}
