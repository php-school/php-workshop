<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Exercise;

/**
 * This interface describes the additional methods a CLI type exercise should implement.
 */
interface CliExercise extends ProvidesSolution
{
    /**
     * This method should return an array of an array of strings.
     * Each set of arguments will be passed to the students solution as command line arguments.
     *
     * @return array<array<string>> An array of string arguments.
     */
    public function getArgs(): array;
}
