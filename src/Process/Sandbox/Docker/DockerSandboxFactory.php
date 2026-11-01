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

    public function create(string $directory, ExerciseScenario $scenario): DockerSandbox
    {
        $port = $scenario->exposesPort() ? $this->portPicker->pick() : null;

        return new DockerSandbox($directory, $port, $this->findDocker(), $this->basePath, $this->getImageName(), $this->portsNetwork, $this->portsHost, $this->portPicker);
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
