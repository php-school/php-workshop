<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Exercise;

use PhpSchool\PhpWorkshop\ExerciseDispatcher;
use PhpSchool\PhpWorkshop\Environment\CgiTestEnvironment;
use Psr\Http\Message\RequestInterface;

/**
 * This interface describes the additional methods a CGI type exercise should implement.
 */
interface CgiExercise extends ProvidesSolution
{
    /**
     * This method should return an instance of CgiTestEnvironment which contains PSR-7 requests,
     * which will be forwarded to the student's solution.
     * Use like:
     *
     * ```
     * return (new CgiTestEnvironment())
     *     ->withExecution($request1)
     * ```
     */
    public function defineTestEnvironment(): CgiTestEnvironment;
}
