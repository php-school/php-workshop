<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Process;

interface Sandbox
{
    public function create(ProcessInput $processInput): SandboxExecution;

    /**
     * A port for code to listen on, free where this sandbox runs it.
     */
    public function pickPort(): int;
}
