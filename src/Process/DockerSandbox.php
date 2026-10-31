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
    private ?string $dockerPath = null;
    private string $basePath;
    private string $projectName;
    private string $portsNetwork;
    private string $portsHost;

    /**
     * @var array<string, string>
     */
    private array $vendorVolumes;

    /**
     * @var array<string, string> folder => container
     */
    private array $containers = [];

    /**
     * @param array<string, string> $vendorVolumes folder => volume, for runs in a folder directly inside it, so vendor
     *   stays on the docker host between runs, eg. ['/tmp/php-school/<student id>' => 'php-school-vendor-<student id>-<exercise>']
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
        $docker = $this->findDocker();
        $directory = $processInput->getWorkingDirectory();
        $isComposer = $processInput->getExecutable() === 'composer';
        $vendorVolume = $this->vendorVolumes[dirname($directory)] ?? null;

        $args = $processInput->getArgs();
        $env = ['COMPOSER_HOME' => '/composer'];
        if ($isComposer) {
            //never run package plugins or scripts
            $args = [...$args, '--no-plugins', '--no-scripts'];
        }

        $envArgs = [];
        foreach ([...$processInput->getEnv(), ...$env] as $key => $value) {
            $envArgs[] = '-e';
            $envArgs[] = $key . '=' . $value;
        }

        //composer needs the internet and exposed ports are set when the container is made, so they get their own.
        //Other runs of a folder share one container, removed when the sandbox is
        if ($isComposer || count($processInput->getExposedPorts()) > 0) {
            $container = $this->containerName();
            $before = $this->startContainer($docker, $container, $processInput, $vendorVolume);
            $after = [new Process([$docker, 'rm', '-f', '-v', $container])];

            if ($isComposer) {
                //ComposerCheck reads composer.lock and looks for vendor. Without a volume vendor comes back too, for
                //the runs after
                array_unshift($after, Process::fromShellCommandline(sprintf(
                    '%s exec %s tar -c -C /solution composer.lock %s | tar -x -C %s',
                    escapeshellarg($docker),
                    $container,
                    $vendorVolume === null ? 'vendor' : '',
                    escapeshellarg($directory),
                ) . ($vendorVolume === null ? '' : sprintf('; mkdir -p %s', escapeshellarg($directory . '/vendor')))));
            }
        } elseif (isset($this->containers[$directory])) {
            $container = $this->containers[$directory];
            $before = [];
            $after = [];
        } else {
            $container = $this->containerName();
            $this->containers[$directory] = $container;
            $before = $this->startContainer($docker, $container, $processInput, $vendorVolume);
            $after = [];
        }

        $exec = new Process(
            [
                $docker,
                'exec',
                '-i',
                ...$envArgs,
                '-w',
                '/solution',
                $container,
                //the docker client being killed does not stop the process, so enforce the timeout inside it too
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
        );

        return new DockerExecution($exec, $before, $after);
    }

    public function __destruct()
    {
        foreach ($this->containers as $container) {
            (new Process([$this->findDocker(), 'rm', '-f', '-v', $container]))->run();
        }
    }

    private function containerName(): string
    {
        return 'php-school-' . bin2hex(random_bytes(8));
    }

    /**
     * Steps that make an idle container and copy the folder into it. Nothing is mounted from this machine, so the
     * docker host can be another one
     *
     * @return list<Process>
     */
    private function startContainer(string $docker, string $container, ProcessInput $processInput, ?string $vendorVolume): array
    {
        $isComposer = $processInput->getExecutable() === 'composer';

        $mounts = [];
        if ($vendorVolume !== null) {
            //only composer writes to it
            $mounts[] = $vendorVolume . ':/solution/vendor' . ($isComposer ? '' : ':ro');
        }

        if ($isComposer) {
            $mounts[] = self::COMPOSER_CACHE_VOLUME . ':/composer';
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

        //each entry rather than the folders, as tar would try to change the image's /solution and /solution/vendor,
        //which it can not
        $directory = $processInput->getWorkingDirectory();
        $files = [];
        foreach (array_diff(scandir($directory) ?: [], ['.', '..']) as $file) {
            if ($file !== 'vendor') {
                $files[] = $file;
            } elseif ($vendorVolume === null) {
                //with a volume, vendor is already in it
                foreach (array_diff(scandir($directory . '/vendor') ?: [], ['.', '..']) as $package) {
                    $files[] = 'vendor/' . $package;
                }
            }
        }

        return [
            new Process([
                $docker,
                'create',
                '--name',
                $container,
                '--init',
                '--user',
                getmyuid() . ':' . getmygid(),
                ...$network,
                '--read-only',
                '--tmpfs',
                '/tmp:rw,nosuid,nodev,size=64m',
                //a volume of its own, so it can be written. It starts as the image's /solution, which a new vendor
                //volume takes its permissions from. docker cp can not write to a read-only container, so files go
                //in with tar
                '-v',
                '/solution',
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
                ...array_merge(...array_map(fn($mount) => ['-v', $mount], $mounts)),
                $this->getImageName(),
                'sleep',
                'infinity',
            ], $this->basePath),
            new Process([$docker, 'start', $container], $this->basePath),
            Process::fromShellCommandline(sprintf(
                'tar -c -C %s -- %s | %s exec -i %s tar -x -m -C /solution',
                escapeshellarg($directory),
                implode(' ', array_map('escapeshellarg', $files)),
                escapeshellarg($docker),
                $container,
            ), $this->basePath),
        ];
    }

    private function findDocker(): string
    {
        $this->dockerPath ??= $this->executableFinder->find('docker');
        if ($this->dockerPath === null) {
            throw ProcessNotFoundException::fromExecutable('docker');
        }

        return $this->dockerPath;
    }
}
