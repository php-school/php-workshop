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
    //a named volume, so it lives on the docker host, which may not be this machine
    private const COMPOSER_CACHE_VOLUME = 'php-school-composer-cache';

    private ExecutableFinder $executableFinder;
    private string $basePath;
    private string $projectName;
    private string $portsNetwork;
    private string $portsHost;

    /**
     * @var array<string, string>
     */
    private array $vendorVolumes;

    /**
     * @param array<string, string> $vendorVolumes folder => volume, for runs in that folder or below it, so vendor stays
     *   on the docker host between runs, eg. ['/tmp/php-school/<student id>' => 'php-school-vendor-<student id>-<exercise>']
     */
    public function __construct(
        string $basePath,
        string $projectName,
        ?ExecutableFinder $executableFinder = null,
        string $portsNetwork = 'bridge',
        string $portsHost = '127.0.0.1',
        array $vendorVolumes = [],
    ) {
        $this->executableFinder = $executableFinder ?? new ExecutableFinder();
        $this->basePath = $basePath;
        $this->projectName = $projectName;
        //for exercises that expose ports, eg a server the check connects to. Pass a network without internet access
        //to stop student code reaching out, the published ports still work
        $this->portsNetwork = $portsNetwork;
        //the address ports are published on, the docker host's private address when it is remote
        $this->portsHost = $portsHost;
        $this->vendorVolumes = $vendorVolumes;
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

    public function getHost(): SandboxHost
    {
        return new SandboxHost($this->portsHost);
    }

    public function create(ProcessInput $processInput): DockerExecution
    {
        $isComposer = $processInput->getExecutable() === 'composer';

        $args = $processInput->getArgs();
        $mounts = [$processInput->getWorkingDirectory() . ':/solution'];

        $vendorVolume = $this->findVendorVolume($processInput->getWorkingDirectory());
        if ($vendorVolume !== null) {
            //only composer writes to it
            $mounts[] = $vendorVolume . ':/solution/vendor' . ($isComposer ? '' : ':ro');
        }

        $env = ['COMPOSER_HOME' => '/composer'];

        if ($isComposer) {
            //we need to mount a volume for composer cache, and never run package plugins or scripts
            $mounts[] = self::COMPOSER_CACHE_VOLUME . ':/composer';
            $args = [...$args, '--no-plugins', '--no-scripts'];
        }

        $network = ['--network', 'none'];
        if ($isComposer) {
            //composer needs to download packages
            $network = ['--network', 'bridge'];
        } elseif (count($processInput->getExposedPorts()) > 0) {
            $network = ['--network', $this->portsNetwork];
            foreach ($processInput->getExposedPorts() as $port) {
                $network[] = '-p';
                $network[] = $this->portsHost . ':' . $port . ':' . $port;
            }
        }

        $envArgs = [];
        foreach ([...$processInput->getEnv(), ...$env] as $key => $value) {
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
                ...array_merge(...array_map(fn($mount) => ['-v', $mount], $mounts)),
                '-w',
                '/solution',
                $this->getImageName(),
                //the docker client being killed does not stop the container, so enforce the timeout inside it too
                'timeout',
                '-s',
                'KILL',
                (string) self::TIMEOUT,
                $processInput->getExecutable(),
                ...$args,
            ],
            $this->basePath,
            null,
            $processInput->getInput(),
            self::TIMEOUT + 5,
        ));
    }

    private function findVendorVolume(string $directory): ?string
    {
        foreach ($this->vendorVolumes as $folder => $volume) {
            if (str_starts_with($directory . '/', rtrim($folder, '/') . '/')) {
                return $volume;
            }
        }

        return null;
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
