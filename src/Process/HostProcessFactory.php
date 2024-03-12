<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Process;

use PhpSchool\PhpWorkshop\Utils\Collection;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

final class HostProcessFactory implements ProcessFactory
{
    private ExecutableFinder $executableFinder;

    public function __construct(ExecutableFinder $executableFinder = null)
    {
        $this->executableFinder = $executableFinder ?? new ExecutableFinder();
    }

    public function composer(string $solutionPath, string $composerCommand, array $composerArgs): Process
    {
        $composer = $this->executableFinder->find('composer');

        if ($composer === null) {
            throw ProcessNotFoundException::fromExecutable('composer');
        }

        return new Process(
            array_merge([$composer, $composerCommand], $composerArgs),
            $solutionPath
        );
    }

    public function phpCli(string $fileName, Collection $args): Process
    {
        $php = $this->executableFinder->find('php');

        if ($php === null) {
            throw ProcessNotFoundException::fromExecutable('php');
        }

        return new Process(
            $args->prepend($fileName)->prepend($php)->getArrayCopy(),
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
            $this->executableFinder->find('php-cgi')
        );

        $cmd = sprintf('echo %s | %s', escapeshellarg($content), $cgiBinary);

        return Process::fromShellCommandline($cmd, null, $env, null, 10);
    }
}
