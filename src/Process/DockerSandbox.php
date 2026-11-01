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
    private PortPicker $portPicker;

    public function __construct(
        string $basePath,
        string $projectName,
        ?ExecutableFinder $executableFinder = null,
        string $portsNetwork = 'bridge',
        string $portsHost = '127.0.0.1',
        ?PortPicker $portPicker = null,
    ) {
        $this->executableFinder = $executableFinder ?? new ExecutableFinder();
        $this->basePath = $basePath;
        $this->projectName = $projectName;
        //for exercises that expose ports, eg a server the check connects to. Pass a network without internet access
        //to stop student code reaching out, the published ports still work
        $this->portsNetwork = $portsNetwork;
        //the address ports are published on, the docker host's private address when it is remote. IPv6 in brackets,
        //eg. [fdaa::2]
        $this->portsHost = $portsHost;
        $this->portPicker = $portPicker ?? new FreePortPicker();
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
        $network = ['--network', 'none'];
        $ports = [];
        if (count($processInput->getExposedPorts()) > 0) {
            $network = ['--network', $this->portsNetwork];
            foreach ($processInput->getExposedPorts() as $port) {
                $hostPort = $this->portPicker->pick();
                $network[] = '-p';
                $network[] = $this->portsHost . ':' . $hostPort . ':' . $port;
                $ports[$port] = $hostPort;
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
        ), [], [], $this->portsHost, $ports);
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
