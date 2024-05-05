<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Process;

use PhpSchool\PhpWorkshop\Utils\Collection;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;
use PhpSchool\PhpWorkshop\Utils\System;

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
        ExecutableFinder $executableFinder = null
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
            $mounts[] = $this->composerCacheDir . ':/root/.composer/cache';
        }

        $env = array_map(function ($key, $value) {
            return sprintf('-e %s=%s', $key, $value);
        }, array_keys($processInput->getEnv()), $processInput->getEnv());

        return new Process(
            [...$this->baseComposeCommand($mounts, $env), 'runtime', $processInput->getExecutable(), ...$processInput->getArgs()],
            $this->basePath,
            ['SOLUTION' => $processInput->getWorkingDirectory()],
            $processInput->getInput(),
            10
        );
    }

    /**
     * @param array<string> $mounts
     * @param array<string> $env
     * @return array<string>
     */
    private function baseComposeCommand(array $mounts, array $env): array
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
            '--rm',
            ...$env,
            '-w',
            '/solution',
            ...array_merge(...array_map(fn ($mount) => ['-v', $mount], $mounts)),
        ];
    }
}
