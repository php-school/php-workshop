<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Exercise;

use PhpSchool\PhpWorkshop\Exercise\Scenario\CliScenario;

/**
 * This interface describes the additional methods a CLI type exercise should implement.
 */
interface CliExercise extends ProvidesSolution
{
    /**
     * This method should return an instance of CliScenario which contains sets of arguments,
     * which will be passed to the students solution as command line arguments.
     *
     * Use like so:
     *
     * ```
     * return (new CliScenario())
     *     ->withExecution(['arg1', 'arg2'])
     *     ->withExecution(['round2-arg1', 'round2-arg2'])
     * ```
     */
    public function defineTestScenario(): CliScenario;
}
