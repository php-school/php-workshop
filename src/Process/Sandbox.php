<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Process;

interface Sandbox
{
    public function create(ProcessInput $processInput): SandboxExecution;

    public function getHost(): SandboxHost;

    /**
     * Removes anything kept between runs, eg. vendor volumes. Called when a verify or run finishes.
     */
    public function cleanup(): void;
}
