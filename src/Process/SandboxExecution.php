<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Process;

/**
 * One run of some code in a sandbox. How many commands that takes is up to the sandbox.
 */
interface SandboxExecution
{
    public function start(): void;

    /**
     * @param (callable(string, string): void)|null $callback receives the output as it comes
     */
    public function wait(?callable $callback = null): int;

    /**
     * @throws \Symfony\Component\Process\Exception\ProcessFailedException if it fails
     */
    public function mustRun(): void;

    public function isSuccessful(): bool;

    public function getOutput(): string;

    public function getErrorOutput(): string;

    /**
     * Where to connect to a port the code listens on, once it has started.
     *
     * @return string eg. '127.0.0.1:54321'
     */
    public function getAddress(int $port): string;
}
