<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Exercise;

use PhpSchool\PhpWorkshop\Exercise\Scenario\CgiScenario;

/**
 * This interface describes the additional methods a CGI type exercise should implement.
 */
interface CgiExercise extends ProvidesSolution
{
    /**
     * This method should return an instance of CgiScenario which contains PSR-7 requests,
     * which will be forwarded to the student's solution.
     *
     * Use like so:
     *
     * ```
     * return (new CgiScenario())
     *     ->withExecution($request1)
     * ```
     */
    public function defineTestScenario(): CgiScenario;
}
