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


    public function create(ProcessInput $processInput): Process
    {
        $executablePath = $this->executableFinder->find($processInput->getExecutable());

        if ($executablePath === null) {
            throw ProcessNotFoundException::fromExecutable($processInput->getExecutable());
        }

        return new Process(
            [$executablePath, ...$processInput->getArgs()],
            $processInput->getWorkingDirectory(),
            $this->getDefaultEnv() + $processInput->getEnv(),
            $processInput->getInput(),
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
