<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Process\Sandbox;

use PhpSchool\PhpWorkshop\Exercise\Scenario\ExerciseScenario;

/**
 * Makes a sandbox for each solution, eg. one for the reference and one for the student's.
 */
interface SandboxFactory
{
    /**
     * With a port free where it runs when the scenario exposes one.
     */
    public function create(string $directory, ExerciseScenario $scenario): Sandbox;
}
