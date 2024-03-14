<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Process;

use PhpSchool\PhpWorkshop\Utils\Collection;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;
use PhpSchool\PhpWorkshop\Utils\System;

final class DockerProcessFactory implements ProcessFactory
{
    private string $basePath;
    private string $docker;
    private string $projectName;

    public function __construct(string $basePath, string $dockerBinaryPath, string $projectName)
    {
        $this->basePath = $basePath;
        $this->docker = $dockerBinaryPath;
        $this->projectName = $projectName;
    }

    /**
     * @return array<string>
     */
    private function baseComposeCommand(): array
    {
        return [
            $this->docker,
            'compose',
            '-p', $this->projectName,
            '-f', '.docker/runtime/docker-compose.yml',
            'run',
            '--rm',
            '-w',
            '/solution',
        ];
    }

    public function composer(string $solutionPath, string $composerCommand, array $composerArgs): Process
    {
        $composerCache = System::tempDir('composer-cache');
        if (!file_exists($composerCache)) {
            mkdir($composerCache, 0777, true);
        }

        return new Process(
            [...$this->baseComposeCommand(), '-v', $composerCache . ':/root/.composer/cache', 'runtime', 'composer', $composerCommand, ...$composerArgs],
            $this->basePath,
            ['SOLUTION' => $solutionPath],
            null,
            120
        );
    }

    public function phpCli(string $fileName, Collection $args): Process
    {
        return new Process(
            [...$this->baseComposeCommand(), 'runtime', 'php', '/solution/' . basename($fileName), ...$args],
            $this->basePath,
            ['SOLUTION' => dirname($fileName)],
            null,
            10
        );
    }

    public function phpCgi(string $solutionPath, array $env, string $content): Process
    {
        $env = array_map(function ($key, $value) {
            return sprintf('-e %s=%s', $key, $value);
        }, array_keys($env), $env);

        $command = [
            ...$this->baseComposeCommand(),
            ...$env,
            '--entrypoint', '/bin/sh -c',
            'runtime',
            sprintf('echo "%s" | php-cgi', escapeshellarg($content)),
            '-dalways_populate_raw_post_data=-1',
            '-dhtml_errors=0',
            '-dexpose_php=0',
        ];

        return new Process(
            $command,
            $this->basePath,
            ['SOLUTION' => $solutionPath],
            null,
            10
        );
    }
}
