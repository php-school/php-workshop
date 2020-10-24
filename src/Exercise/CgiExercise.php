<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Exercise;

use Psr\Http\Message\RequestInterface;

/**
 * This interface describes the additional methods a CGI type exercise should implement.
 */
interface CgiExercise extends ProvidesSolution
{
    /**
     * This method should return an array of PSR-7 requests, which will be forwarded to the student's
     * solution.
     *
     * @return array<RequestInterface> An array of PSR-7 requests.
     */
    public function getRequests(): array;
}
