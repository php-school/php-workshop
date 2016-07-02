<?php

namespace PhpSchool\PhpWorkshop\Exercise;

use Psr\Http\Message\RequestInterface;

/**
 * This interface describes the additional methods a CGI type exercise should implement.
 *
 * @package PhpSchool\PhpWorkshop\Exercise
 */
interface CgiExercise
{
    /**
     * This method should return an array of PSR-7 requests, which will be forwarded to the student's
     * solution.
     *
     * @return RequestInterface[] An array of PSR-7 requests.
     */
    public function getRequests();
}
