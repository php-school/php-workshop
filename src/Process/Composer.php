<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Process;

use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

/**
 * Runs composer on this machine, not in the sandbox. Package plugins and scripts never run, so no package code does,
 * and vendor is copied into the sandbox with the rest of the solution.
 */
final class Composer
{
    //downloading packages can take a while
    private const TIMEOUT = 120;

    private ExecutableFinder $executableFinder;

    public function __construct(?ExecutableFinder $executableFinder = null)
    {
        $this->executableFinder = $executableFinder ?? new ExecutableFinder();
    }

    /**
     * @param list<string> $args eg. ['install']
     * @param array<string, string> $env eg. ['COMPOSER_DISABLE_NETWORK' => '1']
     */
    public function create(array $args, string $directory, array $env = []): Process
    {
        $composer = $this->executableFinder->find('composer');
        if ($composer === null) {
            throw ProcessNotFoundException::fromExecutable('composer');
        }

        return new Process(
            [$composer, ...$args, '--no-interaction', '--no-plugins', '--no-scripts'],
            $directory,
            $env,
            null,
            self::TIMEOUT,
        );
    }
}
