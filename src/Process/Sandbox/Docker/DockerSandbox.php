<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Process\Sandbox\Docker;

use PhpSchool\PhpWorkshop\Process\Sandbox\ProcessInput;
use PhpSchool\PhpWorkshop\Process\Sandbox\Sandbox;
use PhpSchool\PhpWorkshop\Process\Sandbox\SandboxExecution;
use Symfony\Component\Process\Process;

/**
 * Runs the code in a throwaway container with the solution's directory mounted.
 *
 * The workshop only supplies the image. All isolation flags are set here so a workshop cannot loosen them.
 */
final class DockerSandbox implements Sandbox
{
    private const TIMEOUT = 30;

    /**
     * @param string $portsNetwork the network when a port is published, see DockerSandboxFactory
     * @param string $portsHost the address the port is published on, eg. 127.0.0.1
     */
    public function __construct(
        private string $directory,
        private ?int $port,
        private string $docker,
        private string $basePath,
        private string $image,
        private string $portsNetwork,
        private string $portsHost,
    ) {}

    public function execute(ProcessInput $processInput): SandboxExecution
    {
        $network = ['--network', 'none'];
        if ($this->port !== null) {
            //the same port on the docker host, the factory picked one free there
            $network = ['--network', $this->portsNetwork, '-p', $this->portsHost . ':' . $this->port . ':' . $this->port];
        }

        $envArgs = [];
        foreach ($processInput->getEnv() as $key => $value) {
            $envArgs[] = '-e';
            $envArgs[] = $key . '=' . $value;
        }

        return new SandboxExecution(new Process(
            [
                $this->docker,
                'run',
                '--rm',
                '-i',
                '--init',
                '--user',
                getmyuid() . ':' . getmygid(),
                ...$network,
                '--read-only',
                '--tmpfs',
                '/tmp:rw,nosuid,nodev,size=64m',
                '--cap-drop',
                'ALL',
                '--security-opt',
                'no-new-privileges',
                '--pids-limit',
                '64',
                '--memory',
                '256m',
                '--memory-swap',
                '256m',
                '--cpus',
                '1',
                ...$envArgs,
                '-v',
                $this->directory . ':/solution',
                '-w',
                '/solution',
                $this->image,
                //the docker client being killed does not stop the container, so enforce the timeout inside it too
                'timeout',
                '-s',
                'KILL',
                (string) self::TIMEOUT,
                $processInput->getExecutable(),
                ...$processInput->getArgs(),
            ],
            $this->basePath,
            null,
            $processInput->getInput(),
            self::TIMEOUT + 5,
        ), $this->portsHost, $this->port);
    }

    public function getPort(): int
    {
        return $this->port ?? throw new \RuntimeException('The scenario does not expose a port');
    }
}
