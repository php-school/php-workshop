<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Process\Sandbox;

/**
 * Where one solution's code runs. Every execution of it happens here and listens on the same port.
 */
interface Sandbox
{
    public function execute(ProcessInput $processInput): SandboxExecution;

    /**
     * The port the code listens on, eg. to pass it in the args. Only when the scenario exposes a port.
     */
    public function getPort(): int;

    /**
     * Once every execution is done: the files the code wrote are in the directory again and the port is free.
     */
    public function finish(): void;
}
