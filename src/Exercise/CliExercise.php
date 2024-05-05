<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Exercise;

use PhpSchool\PhpWorkshop\ExerciseDispatcher;
use PhpSchool\PhpWorkshop\Environment\CliTestEnvironment;

/**
 * This interface describes the additional methods a CLI type exercise should implement.
 */
interface CliExercise extends ProvidesSolution
{
    /**
     * This method should return an instance of CliTestEnvironment which contains sets of arguments,
     * which will be passed to the students solution as command line arguments to the student's solution.
     *
     * Use like:
     *
     * ```
     * return (new CliTestEnvironment())
     *     ->withExecution(['arg1', 'arg2'])
     *     ->withExecution(['round2-arg1', 'round2-arg2'])
     * ```
     */
    public function defineTestEnvironment(): CliTestEnvironment;
}
