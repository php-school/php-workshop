<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Process;

use Symfony\Component\Process\Process;

final class HostProcessFactory implements ProcessFactory
{
    private string $cliBinary;
    private string $cgiBinary;
    private string $composerBinary;

    public function __construct(string $phpCliBinary, string $phpCgiBinary, string $composerBinary)
    {
        $this->cliBinary = $phpCliBinary;
        $this->cgiBinary = $phpCgiBinary;
        $this->composerBinary = $composerBinary;
    }

    public function composer(string $solutionPath, string $composerCommand, array $composerArgs): Process
    {
        $process = new Process(
            array_merge([$this->composerBinary, $composerCommand], $composerArgs),
            $solutionPath
        );
    }

    public function phpCli(string $fileName, array $args): Process
    {
        return new Process(
            array_merge([$this->cliBinary], $args),
            dirname($fileName),
            $this->getDefaultEnv() + ['XDEBUG_MODE' => 'off'],
            null,
            10
        );
    }

    /**
     * We need to reset env entirely, because Symfony inherits it. We do that by setting all
     * the current env vars to false
     *
     * @return array<string, false>
     */
    private function getDefaultEnv(): array
    {
        $env = array_map(fn () => false, $_ENV);
        $env + array_map(fn () => false, $_SERVER);

        return $env;
    }

    public function phpCgi(string $solutionPath, array $env, string $content): Process
    {
        $cgiBinary = sprintf(
            '%s -dalways_populate_raw_post_data=-1 -dhtml_errors=0 -dexpose_php=0',
            $this->cgiBinary
        );

        $cmd = sprintf('echo %s | %s', escapeshellarg($content), $cgiBinary);

        return Process::fromShellCommandline($cmd, null, $env, null, 10);
    }
}
