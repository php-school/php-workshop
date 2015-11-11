<?php

namespace PhpSchool\PhpWorkshop\ExerciseCheck;

use Psr\Http\Message\RequestInterface;

/**
 * Interface CgiOutputExerciseCheck
 * @package PhpSchool\PhpWorkshop\ExerciseCheck
 */
interface CgiOutputExerciseCheck
{
    /**
     * @return RequestInterface[]
     */
    public function getRequests();
}
