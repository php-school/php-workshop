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

    /**
     * @param array<string> $args
     */
    public function create(
        string $executable,
        array $args,
        string $workingDirectory,
        array $env,
        string $input = null
    ): Process {
        $executablePath = $this->executableFinder->find($executable);

        if ($executablePath === null) {
            throw ProcessNotFoundException::fromExecutable($executable);
        }

        return new Process(
            [$executable, ...$args],
            $workingDirectory,
            $this->getDefaultEnv() + $env,
            $input,
            10,
        );
    }

    /**
     * @return array<string, false>
     */
    private function getDefaultEnv(): array
    {
        $env = array_map(fn () => false, $_ENV);
        $env + array_map(fn () => false, $_SERVER);

        return $env;
    }
}
