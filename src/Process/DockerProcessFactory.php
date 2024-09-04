<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Process;

use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

final class DockerProcessFactory implements ProcessFactory
{
    private ExecutableFinder $executableFinder;
    private string $basePath;
    private string $projectName;
    private string $composerCacheDir;

    public function __construct(
        string $basePath,
        string $projectName,
        string $composerCacheDir,
        ExecutableFinder $executableFinder = null,
    ) {
        $this->executableFinder = $executableFinder ?? new ExecutableFinder();
        $this->basePath = $basePath;
        $this->projectName = $projectName;
        $this->composerCacheDir = $composerCacheDir;
    }

    public function create(ProcessInput $processInput): Process
    {
        $mounts = [];
        if ($processInput->getExecutable() === 'composer') {
            //we need to mount a volume for composer cache
            $mounts[] = $this->composerCacheDir . ':/tmp/composer';
        }

        $env = [];
        foreach ($processInput->getEnv() as $key => $value) {
            $env[] = '-e';
            $env[] = $key . '=' . $value;
        }

        $ports = [];
        foreach ($processInput->getExposedPorts() as $port) {
            $ports[] = '-p';
            $ports[] = $port . ':' . $port;
        }

        $env[]  = '-e';
        $env[]  = 'COMPOSER_HOME=/tmp/composer';

        $p = new Process(
            [
                ...$this->baseComposeCommand($mounts, $env, $ports),
                'runtime',
                $processInput->getExecutable(),
                ...$processInput->getArgs(),
            ],
            $this->basePath,
            [
                'SOLUTION' => $processInput->getWorkingDirectory(),
                'UID' => getmyuid(),
                'GID' => getmygid(),
            ],
            $processInput->getInput(),
            30,
        );

        return $p;
    }

    /**
     * @param array<string> $mounts
     * @param array<string> $env
     * @param list<string> $ports
     * @return array<string>
     */
    private function baseComposeCommand(array $mounts, array $env, array $ports): array
    {
        $dockerPath = $this->executableFinder->find('docker');
        if ($dockerPath === null) {
            throw ProcessNotFoundException::fromExecutable('docker');
        }

        return [
            $dockerPath,
            'compose',
            '-p',
            $this->projectName,
            '-f',
            '.docker/runtime/docker-compose.yml',
            'run',
            '--user',
            getmyuid() . ':' . getmygid(),
            '--rm',
            ...$env,
            ...$ports,
            '-w',
            '/solution',
            ...array_merge(...array_map(fn($mount) => ['-v', $mount], $mounts)),
        ];
    }
}
