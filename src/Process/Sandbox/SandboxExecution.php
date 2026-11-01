<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Process\Sandbox;

use Symfony\Component\Process\Process;

/**
 * One run of some code in a sandbox.
 */
final class SandboxExecution
{
    /**
     * @param string $host where the port is reachable, eg. 127.0.0.1 or the docker host's address
     * @param int|null $port the code listens on, eg. 54321
     */
    public function __construct(public readonly Process $process, private string $host = '127.0.0.1', private ?int $port = null) {}

    public function start(): void
    {
        $this->process->start();
    }

    /**
     * @param (callable(string, string): void)|null $callback receives the output as it comes
     */
    public function wait(?callable $callback = null): int
    {
        return $this->process->wait($callback);
    }

    /**
     * @throws \Symfony\Component\Process\Exception\ProcessFailedException if it fails
     */
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

    /**
     * Where to connect to the port the code listens on, once it has started.
     *
     * @return string eg. '127.0.0.1:54321'
     */
    public function getAddress(): string
    {
        return $this->host . ':' . ($this->port ?? throw new \RuntimeException('The code does not listen on a port'));
    }
}
