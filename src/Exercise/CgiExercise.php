<?php

namespace PhpSchool\PhpWorkshop\Exercise;

use PhpSchool\PhpWorkshop\Solution\SolutionInterface;
use Psr\Http\Message\RequestInterface;

/**
 * This interface describes the additional methods a CGI type exercise should implement.
 *
 * @package PhpSchool\PhpWorkshop\Exercise
 */
interface CgiExercise
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
     * This method should return an array of PSR-7 requests, which will be forwarded to the student's
     * solution.
     *
     * @return RequestInterface[] An array of PSR-7 requests.
     */
    public function getRequests();
}
