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

        //composer needs the internet, and published ports would clash between folders, so they get a container just
        //for the run
        if ($isComposer || count($processInput->getExposedPorts()) > 0) {
            $container = $this->newContainerName();
            $after = $isComposer ? [$this->copyOut($container, $directory), $this->remove($container)] : [$this->remove($container)];

            return new DockerExecution($this->exec($container, $processInput), $this->start($container, $processInput), $after);
        }

        //other runs of a folder share one, removed with the sandbox
        if (isset($this->containers[$directory])) {
            return new DockerExecution($this->exec($this->containers[$directory], $processInput));
        }

        $container = $this->containers[$directory] = $this->newContainerName();

        return new DockerExecution($this->exec($container, $processInput), $this->start($container, $processInput));
    }

    public function __destruct()
    {
        foreach ($this->containers as $container) {
            $this->remove($container)->run();
        }
    }

    /**
     * Make an idle container and copy the folder into it. Nothing is mounted from this machine, so the docker host
     * can be another one.
     *
     * @return list<Process>
     */
    private function start(string $container, ProcessInput $processInput): array
    {
        $isComposer = $processInput->getExecutable() === 'composer';
        $ports = $processInput->getExposedPorts();
        $vendorVolume = $this->vendorVolume($processInput->getWorkingDirectory());

        $network = match (true) {
            $isComposer => ['--network', 'bridge', '-v', self::COMPOSER_CACHE_VOLUME . ':/composer'],
            count($ports) > 0 => ['--network', $this->portsNetwork, ...$this->publish($ports)],
            default => ['--network', 'none'],
        };

        //only composer writes to it
        $vendor = match (true) {
            $vendorVolume === null => [],
            $isComposer => ['-v', $vendorVolume . ':/solution/vendor'],
            default => ['-v', $vendorVolume . ':/solution/vendor:ro'],
        };

        return [
            $this->docker([
                'create', '--name', $container, '--user', getmyuid() . ':' . getmygid(),
                ...self::ISOLATION,
                //a volume of its own, so it can be written. It starts as the image's /solution, so a new vendor
                //volume gets its permissions
                '-v', '/solution',
                ...$network,
                ...$vendor,
                $this->getImageName(), 'sleep', 'infinity',
            ]),
            $this->docker(['start', $container]),
            $this->copyIn($container, $processInput->getWorkingDirectory()),
        ];
    }

    private function exec(string $container, ProcessInput $processInput): Process
    {
        $args = $processInput->getArgs();
        if ($processInput->getExecutable() === 'composer') {
            //never run package plugins or scripts
            $args = [...$args, '--no-plugins', '--no-scripts'];
        }

        $env = [];
        foreach ([...$processInput->getEnv(), 'COMPOSER_HOME' => '/composer'] as $key => $value) {
            array_push($env, '-e', $key . '=' . $value);
        }

        return $this->docker(
            [
                'exec', '-i', ...$env, '-w', '/solution', $container,
                //the docker client being killed does not stop the process, so enforce the timeout inside it too
                'timeout', '-s', 'KILL', (string) self::TIMEOUT,
                $processInput->getExecutable(), ...$args,
            ],
            $processInput->getInput(),
            self::TIMEOUT + 5,
        );
    }

    /**
     * With tar, as docker cp can not write to a read-only container. Files only, as tar can not change the folders
     * the image made, /solution and /solution/vendor. A vendor volume already has vendor.
     */
    private function copyIn(string $container, string $directory): Process
    {
        $find = $this->vendorVolume($directory) === null ? 'find . ! -type d' : 'find . -path ./vendor -prune -o ! -type d -print';

        return Process::fromShellCommandline(
            sprintf('%s | tar -c -T - | %s exec -i %s tar -x -C /solution', $find, escapeshellarg($this->findDocker()), $container),
            $directory,
        );
    }

    /**
     * composer.lock for ComposerCheck, and vendor for the runs after.
     */
    private function copyOut(string $container, string $directory): Process
    {
        return Process::fromShellCommandline(
            sprintf('%s exec %s tar -c -C /solution composer.lock vendor | tar -x', escapeshellarg($this->findDocker()), $container),
            $directory,
        );
    }

    private function remove(string $container): Process
    {
        return $this->docker(['rm', '-f', '-v', $container]);
    }

    /**
     * @param list<int> $ports
     * @return list<string> eg. ['-p', '127.0.0.1:8000:8000']
     */
    private function publish(array $ports): array
    {
        return array_merge(...array_map(fn(int $port) => ['-p', $this->portsHost . ':' . $port . ':' . $port], $ports));
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
