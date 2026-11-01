<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Process;

use Symfony\Component\Process\Process;

final class HostExecution implements SandboxExecution
{
    public function __construct(public readonly Process $process, private ?int $port = null) {}

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

    public function getAddress(): string
    {
        return '127.0.0.1:' . $this->getPort();
    }

    public function getPort(): int
    {
        return $this->port ?? throw new \RuntimeException('The code does not listen on a port');
    }
}
