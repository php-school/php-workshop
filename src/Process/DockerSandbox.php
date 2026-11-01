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
    private const ISOLATION = [
        '--init',
        '--read-only',
        '--tmpfs', '/tmp:rw,nosuid,nodev,size=64m',
        '--cap-drop', 'ALL',
        '--security-opt', 'no-new-privileges',
        '--pids-limit', '64',
        '--memory', '256m',
        '--memory-swap', '256m',
        '--cpus', '1',
    ];

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
        return $this->docker(['build', '-t', $this->getImageName(), '.docker/runtime'], null, 300);
    }

    public function getHost(): SandboxHost
    {
        return new SandboxHost($this->portsHost);
    }

    public function create(ProcessInput $processInput): DockerExecution
    {
        $directory = $processInput->getWorkingDirectory();
        $isComposer = $processInput->getExecutable() === 'composer';

        //composer needs the internet and ports are published when a container is made, so they get a container of
        //their own. Other runs of a folder share one, removed with the sandbox
        if ($isComposer || count($processInput->getExposedPorts()) > 0) {
            $container = $this->newContainerName();
            $before = $this->startContainer($container, $processInput);
            $after = [$this->docker(['rm', '-f', '-v', $container])];
        } elseif (isset($this->containers[$directory])) {
            $container = $this->containers[$directory];
            $before = [];
            $after = [];
        } else {
            $container = $this->containers[$directory] = $this->newContainerName();
            $before = $this->startContainer($container, $processInput);
            $after = [];
        }

        $args = $processInput->getArgs();
        if ($isComposer) {
            //never run package plugins or scripts
            $args = [...$args, '--no-plugins', '--no-scripts'];
            array_unshift($after, $this->copyOut($container, $directory));
        }

        $env = [];
        foreach ([...$processInput->getEnv(), 'COMPOSER_HOME' => '/composer'] as $key => $value) {
            $env[] = '-e';
            $env[] = $key . '=' . $value;
        }

        $exec = $this->docker(
            [
                'exec', '-i', ...$env, '-w', '/solution', $container,
                //the docker client being killed does not stop the process, so enforce the timeout inside it too
                'timeout', '-s', 'KILL', (string) self::TIMEOUT,
                $processInput->getExecutable(), ...$args,
            ],
            $processInput->getInput(),
            self::TIMEOUT + 5,
        );

        return new DockerExecution($exec, $before, $after);
    }

    public function __destruct()
    {
        foreach ($this->containers as $container) {
            $this->docker(['rm', '-f', '-v', $container])->run();
        }
    }

    /**
     * Make an idle container and copy the folder into it. Nothing is mounted from this machine, so the docker host
     * can be another one.
     *
     * @return list<Process>
     */
    private function startContainer(string $container, ProcessInput $processInput): array
    {
        $isComposer = $processInput->getExecutable() === 'composer';
        $vendorVolume = $this->vendorVolume($processInput->getWorkingDirectory());

        $options = ['--network', 'none'];
        if ($isComposer) {
            //composer downloads packages
            $options = ['--network', 'bridge', '-v', self::COMPOSER_CACHE_VOLUME . ':/composer'];
        } elseif (count($processInput->getExposedPorts()) > 0) {
            $options = ['--network', $this->portsNetwork];
            foreach ($processInput->getExposedPorts() as $port) {
                array_push($options, '-p', $this->portsHost . ':' . $port . ':' . $port);
            }
        }

        if ($vendorVolume !== null) {
            //only composer writes to it
            array_push($options, '-v', $vendorVolume . ':/solution/vendor' . ($isComposer ? '' : ':ro'));
        }

        return [
            $this->docker([
                'create', '--name', $container, '--user', getmyuid() . ':' . getmygid(),
                ...self::ISOLATION,
                //a volume of its own, so it can be written. It starts as the image's /solution, so a new vendor
                //volume gets its permissions
                '-v', '/solution',
                ...$options,
                $this->getImageName(), 'sleep', 'infinity',
            ]),
            $this->docker(['start', $container]),
            $this->copyIn($container, $processInput->getWorkingDirectory()),
        ];
    }

    /**
     * With tar, as docker cp can not write to a read-only container.
     */
    private function copyIn(string $container, string $directory): Process
    {
        //each entry rather than the folders, as tar can not change the image's /solution and /solution/vendor.
        //A vendor volume already has vendor
        $files = array_diff(scandir($directory) ?: [], ['.', '..', 'vendor']);
        if ($this->vendorVolume($directory) === null && is_dir($directory . '/vendor')) {
            foreach (array_diff(scandir($directory . '/vendor') ?: [], ['.', '..']) as $package) {
                $files[] = 'vendor/' . $package;
            }
        }

        return $this->pipe(
            ['tar', '-c', '-C', $directory, '--', ...$files],
            [$this->findDocker(), 'exec', '-i', $container, 'tar', '-x', '-m', '-C', '/solution'],
        );
    }

    /**
     * composer.lock for ComposerCheck, and vendor for the runs after. A vendor volume keeps vendor, so only the empty
     * folder comes back, which ComposerCheck looks for.
     */
    private function copyOut(string $container, string $directory): Process
    {
        $vendor = $this->vendorVolume($directory) === null ? ['vendor'] : ['--no-recursion', 'vendor'];

        return $this->pipe(
            [$this->findDocker(), 'exec', $container, 'tar', '-c', '-C', '/solution', 'composer.lock', ...$vendor],
            ['tar', '-x', '-C', $directory],
        );
    }

    /**
     * @param list<string> $from
     * @param list<string> $to
     */
    private function pipe(array $from, array $to): Process
    {
        //Process escapes each argument
        $commandLine = (new Process($from))->getCommandLine() . ' | ' . (new Process($to))->getCommandLine();

        return Process::fromShellCommandline($commandLine, $this->basePath);
    }

    /**
     * @param list<string> $args
     */
    private function docker(array $args, ?string $input = null, ?float $timeout = 60): Process
    {
        return new Process([$this->findDocker(), ...$args], $this->basePath, null, $input, $timeout);
    }

    private function vendorVolume(string $directory): ?string
    {
        return $this->vendorVolumes[dirname($directory)] ?? null;
    }

    private function newContainerName(): string
    {
        return 'php-school-' . bin2hex(random_bytes(8));
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
