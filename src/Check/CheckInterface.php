<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Check;

/**
 * Base Interface for Checks.
 */
interface CheckInterface
{
    /**
     * Return the check's name
     */
    public function getName(): string;

    /**
     * This returns the interface the exercise should implement
     * when requiring this check. It should be the FQCN of the interface.
     */
    public function getExerciseInterface(): string;
}
