<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Process;

use PhpSchool\PhpWorkshop\Exercise\Scenario\ExerciseScenario;

interface Sandbox
{
    public function create(Workspace $workspace, ProcessInput $processInput): SandboxExecution;

    /**
     * Sets up a solution's directory before its runs, with a port free where this sandbox runs it when the scenario exposes one.
     */
    public function prepare(string $directory, ExerciseScenario $scenario): Workspace;
}
