<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Process;

interface Sandbox
{
    public function create(Workspace $workspace, ProcessInput $processInput): SandboxExecution;

    /**
     * Sets up a solution's directory before its runs, with a port free where this sandbox runs it when asked for.
     */
    public function prepare(string $directory, bool $exposePort): Workspace;
}
