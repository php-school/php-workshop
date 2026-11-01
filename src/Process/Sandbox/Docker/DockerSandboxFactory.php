<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Process\Sandbox\Docker;

use PhpSchool\PhpWorkshop\Process\ProcessNotFoundException;
use PhpSchool\PhpWorkshop\Process\Sandbox\SandboxFactory;
use PhpSchool\PhpWorkshop\Exercise\Scenario\ExerciseScenario;
use PhpSchool\PhpWorkshop\Process\Port\FreePortPicker;
use PhpSchool\PhpWorkshop\Process\Port\PortPicker;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

/**
 * Makes docker sandboxes from the workshop's `.docker/runtime/Dockerfile` image.
 */
final class DockerSandboxFactory implements SandboxFactory
{
    //seconds, longer than every execution of a solution takes. LockPortPicker holds the port as long
    private const LIFETIME = 300;

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

    /**
     * Starts the solution's container and copies its directory in. Nothing is mounted from this machine, so the docker
     * host can be another one.
     */
    public function create(string $directory, ExerciseScenario $scenario): DockerSandbox
    {
        $docker = $this->findDocker();
        $port = $scenario->exposesPort() ? $this->portPicker->pick() : null;
        $container = 'php-school-' . bin2hex(random_bytes(8));

        $network = ['--network', 'none'];
        if ($port !== null) {
            //the same port on the docker host, the picker picked one free there
            $network = ['--network', $this->portsNetwork, '-p', $this->portsHost . ':' . $port . ':' . $port];
        }

        //all isolation flags are set here so a workshop cannot loosen them. It removes itself after LIFETIME seconds
        //when the sandbox is never finished, eg. a crash
        (new Process([
            $docker, 'run', '-d', '--rm', '--name', $container, '--init', '--user', getmyuid() . ':' . getmygid(),
            ...$network,
            '--read-only', '--tmpfs', '/tmp:rw,nosuid,nodev,size=64m', '--cap-drop', 'ALL',
            '--security-opt', 'no-new-privileges', '--pids-limit', '64', '--memory', '256m', '--memory-swap', '256m',
            '--cpus', '1',
            //a volume of its own, so the directory can be copied in
            '-v', '/solution', '-w', '/solution',
            $this->getImageName(), 'sleep', (string) self::LIFETIME,
        ]))->mustRun();

        //tar, as docker cp can not write to a read-only container. Files only, as tar can not change the folders the
        //image made, eg. /solution
        Process::fromShellCommandline(
            sprintf('find . ! -type d | tar -c -T - | %s exec -i %s tar -x -C /solution', escapeshellarg($docker), $container),
            $directory,
        )->mustRun();

        return new DockerSandbox($directory, $port, $docker, $container, $this->portsHost, $this->portPicker);
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
