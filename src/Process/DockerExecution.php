<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Process;

use Symfony\Component\Process\Process;

final class DockerExecution implements SandboxExecution
{
    public function __construct(public readonly Process $process) {}

    public function start(): void
    {
        $this->process->start();
    }

    public function wait(?callable $callback = null): int
    {
        return $this->process->wait($callback);
    }

    public function mustRun(): void
    {
        $this->process->mustRun();
    }

    public function isSuccessful(): bool
    {
        return $this->process->isSuccessful();
    }

    public function getOutput(): string
    {
        return $this->process->getOutput();
    }

    public function getErrorOutput(): string
    {
        return $this->process->getErrorOutput();
    }
}
