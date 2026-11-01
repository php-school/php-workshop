<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Process\Sandbox\Docker;

use PhpSchool\PhpWorkshop\ExerciseRunner\Context\ExecutionContext;
use PhpSchool\PhpWorkshop\Process\Port\PortPicker;
use PhpSchool\PhpWorkshop\Process\Sandbox\ProcessInput;
use PhpSchool\PhpWorkshop\Process\Sandbox\Sandbox;
use PhpSchool\PhpWorkshop\Process\Sandbox\SandboxExecution;
use Symfony\Component\Process\Process;

/**
 * Runs the code in the solution's container, see DockerSandboxFactory. Each execution is a `docker exec`.
 */
final class DockerSandbox implements Sandbox
{
    private const TIMEOUT = 30;

    /**
     * @param string $container the container's name, eg. php-school-1a2b3c4d5e6f7a8b
     * @param string $portsHost the address the port is published on, eg. 127.0.0.1
     */
    public function __construct(
        private string $directory,
        private ?int $port,
        private string $docker,
        private string $container,
        private string $portsHost,
        private PortPicker $portPicker,
    ) {}

    public function execute(ProcessInput $processInput): SandboxExecution
    {
        $envArgs = [];
        foreach ($processInput->getEnv() as $key => $value) {
            array_push($envArgs, '-e', $key . '=' . $value);
        }

        return new SandboxExecution(new Process(
            [
                $this->docker, 'exec', '-i', ...$envArgs, $this->container,
                //the docker client being killed does not stop the process, so enforce the timeout inside it too
                'timeout', '-s', 'KILL', (string) self::TIMEOUT,
                $processInput->getExecutable(), ...$processInput->getArgs(),
            ],
            $this->directory,
            null,
            $processInput->getInput(),
            self::TIMEOUT + 5,
        ), $this->portsHost, $this->port);
    }

    public function getPort(): int
    {
        return $this->port ?? throw new \RuntimeException('The scenario does not expose a port');
    }

    /**
     * The directory is mounted, so the solution files are there already.
     */
    public function finish(ExecutionContext $context): void
    {
        try {
            //the files the code wrote, eg. a database a check reads. Files only, so tar leaves the folder's mode alone.
            //Not vendor, composer runs on this machine
            Process::fromShellCommandline(
                sprintf(
                    "%s exec %s sh -c 'find . -path ./vendor -prune -o ! -type d -print | tar -c -T -' | tar -x",
                    escapeshellarg($this->docker),
                    $this->container,
                ),
                $this->directory,
            )->mustRun();
        } finally {
            (new Process([$this->docker, 'rm', '-f', '-v', $this->container]))->run();

            if ($this->port !== null) {
                $this->portPicker->release($this->port);
            }
        }
    }
}
