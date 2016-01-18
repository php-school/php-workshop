<?php

namespace PhpSchool\PhpWorkshop\Exercise;

use Psr\Http\Message\RequestInterface;

/**
 * Interface CliExercise
 * @package PhpSchool\PhpWorkshop\Exercise
 */
interface CliExercise
{
    /**
     * @return string[]
     */
    public function getArgs();
}
