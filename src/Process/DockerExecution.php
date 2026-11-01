<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Process;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

/**
 * The main process plus the docker steps around it, eg. `create` and `cp` before, `rm` after.
 */
final class DockerExecution implements SandboxExecution
{
    /**
     * @param list<Process> $before
     * @param list<Process> $after
     * @param array<int, string> $addresses where each port is published, eg. [54321 => '10.0.0.5:47213']
     */
    public function __construct(
        public readonly Process $process,
        public readonly array $before = [],
        public readonly array $after = [],
        private array $addresses = [],
    ) {}

    public function start(): void
    {
        foreach ($this->before as $step) {
            $step->mustRun();
        }

        $this->process->start();
    }

    public function wait(?callable $callback = null): int
    {
        try {
            return $this->process->wait($callback);
        } finally {
            $this->runAfter();
        }
    }

    public function mustRun(): void
    {
        $this->start();
        $this->wait();

        if (!$this->process->isSuccessful()) {
            throw new ProcessFailedException($this->process);
        }
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

    public function getAddress(int $port): string
    {
        return $this->addresses[$port] ?? throw new \RuntimeException(sprintf('Port %d is not published', $port));
    }

    private function runAfter(): void
    {
        foreach ($this->after as $step) {
            //cleanup, so keep going if one fails
            $step->run();
        }
    }
}
