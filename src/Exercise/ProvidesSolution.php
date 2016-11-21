<?php

namespace PhpSchool\PhpWorkshop\Exercise;

use PhpSchool\PhpWorkshop\Solution\SolutionInterface;

/**
 * @author Aydin Hassan <aydin@hotmail.co.uk>
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