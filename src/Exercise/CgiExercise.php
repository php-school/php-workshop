<?php

namespace PhpSchool\PhpWorkshop\Exercise;

use Psr\Http\Message\RequestInterface;

/**
 * Interface CgiExercise
 * @package PhpSchool\PhpWorkshop\Exercise
 */
interface CgiExercise
{
    /**
     * @return RequestInterface[]
     */
    public function getRequests();
}
