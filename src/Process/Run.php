<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Process;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

/**
 * A process to run, plus any steps the sandbox needs around it.
 *
 * Eg for docker: before = [docker create, docker cp in], process = docker start, after = [docker rm]
 */
final class Run
{
    /**
     * @param list<Process> $before run in order before the process starts, each must succeed
     * @param list<Process> $after run in order once the process finished, even if it failed
     */
    public function __construct(
        public readonly Process $process,
        public readonly array $before = [],
        public readonly array $after = [],
    ) {}

    public function start(): void
    {
        try {
            foreach ($this->before as $step) {
                $step->mustRun();
            }
        } catch (\Throwable $e) {
            $this->finish();
            throw $e;
        }

        $this->process->start();
    }

    /**
     * @param (callable(string, string): void)|null $callback receives the output as it comes
     */
    public function wait(?callable $callback = null): int
    {
        try {
            return $this->process->wait($callback);
        } finally {
            $this->finish();
        }
    }

    /**
     * @throws ProcessFailedException if the process fails
     */
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

    private function finish(): void
    {
        foreach ($this->after as $step) {
            $step->run();
        }
    }
}
