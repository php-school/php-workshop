<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Process;

interface Sandbox
{
    public function create(ProcessInput $processInput): SandboxExecution;

    public function getHost(): SandboxHost;
}
