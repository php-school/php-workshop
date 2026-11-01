<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Process;

use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

/**
 * Runs processes in a throwaway container built from the workshop's `.docker/runtime/Dockerfile`.
 *
 * The workshop only supplies the image. All isolation flags are set here so a workshop cannot loosen them.
 */
final class DockerSandbox implements Sandbox
{
    private const TIMEOUT = 30;

    private ExecutableFinder $executableFinder;
    private string $basePath;
    private string $projectName;
    private string $portsNetwork;
    private string $portsHost;

    public function __construct(
        string $basePath,
        string $projectName,
        ?ExecutableFinder $executableFinder = null,
        string $portsNetwork = 'bridge',
        string $portsHost = '127.0.0.1',
    ) {
        $this->executableFinder = $executableFinder ?? new ExecutableFinder();
        $this->basePath = $basePath;
        $this->projectName = $projectName;
        //for exercises that expose ports, eg a server the check connects to. Pass a network without internet access
        //to stop student code reaching out, the published ports still work
        $this->portsNetwork = $portsNetwork;
        //the address ports are published on, the docker host's private address when it is remote
        $this->portsHost = $portsHost;
    }

    public function getImageName(): string
    {
        return strtolower($this->projectName) . '-runtime';
    }

    /**
     * The process that builds the runtime image. Run it once on install/deploy, not per request.
     */
    public function createBuildProcess(): Process
    {
        return new Process(
            [$this->findDocker(), 'build', '-t', $this->getImageName(), '.docker/runtime'],
            $this->basePath,
            null,
            null,
            300,
        );
    }

    public function create(ProcessInput $processInput): DockerExecution
    {
        $container = 'php-school-' . bin2hex(random_bytes(8));

        $network = ['--network', 'none'];
        if (count($processInput->getExposedPorts()) > 0) {
            $network = ['--network', $this->portsNetwork];
            foreach ($processInput->getExposedPorts() as $port) {
                //no host port, docker picks a free one, see getAddress()
                $network[] = '-p';
                $network[] = $this->portsHost . '::' . $port;
            }
        }

        $envArgs = [];
        foreach ($processInput->getEnv() as $key => $value) {
            $envArgs[] = '-e';
            $envArgs[] = $key . '=' . $value;
        }

        //no steps around it yet, create/cp/rm come when files are copied in instead of mounted
        return new DockerExecution(new Process(
            [
                $this->findDocker(),
                'run',
                '--rm',
                '--name',
                $container,
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
                $processInput->getWorkingDirectory() . ':/solution',
                '-w',
                '/solution',
                $this->getImageName(),
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
        ), [], [], fn(int $port) => $this->getAddress($container, $port));
    }

    /**
     * Asks docker which host port it published a container's port on. Waits a little, as the container may still be
     * starting.
     */
    private function getAddress(string $container, int $port): string
    {
        for ($try = 0; $try < 20; $try++) {
            //eg. 127.0.0.1:54321
            $process = new Process([$this->findDocker(), 'port', $container, (string) $port], $this->basePath);
            $process->run();

            if ($process->isSuccessful() && trim($process->getOutput()) !== '') {
                $published = strtok(trim($process->getOutput()), "\n");
                $host = str_contains($this->portsHost, ':') ? '[' . $this->portsHost . ']' : $this->portsHost;

                return $host . ':' . substr((string) $published, (int) strrpos((string) $published, ':') + 1);
            }

            usleep(100000);
        }

        throw new \RuntimeException(sprintf('Port %d of the sandbox was not published', $port));
    }

    private function findDocker(): string
    {
        $dockerPath = $this->executableFinder->find('docker');
        if ($dockerPath === null) {
            throw ProcessNotFoundException::fromExecutable('docker');
        }

        return $dockerPath;
    }
}
