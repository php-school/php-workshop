<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Process;

use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

final class HostSandbox implements Sandbox
{
    /**
     * Inherited env vars that are kept, everything else is unset so we don't leak secrets to the process
     */
    private const ALLOWED_ENV = ['PATH', 'HOME', 'COMPOSER_HOME'];

    private ExecutableFinder $executableFinder;

    public function __construct(?ExecutableFinder $executableFinder = null)
    {
        $this->executableFinder = $executableFinder ?? new ExecutableFinder();
    }


    public function create(ProcessInput $processInput): Run
    {
        $executablePath = $this->executableFinder->find($processInput->getExecutable());

        if ($executablePath === null) {
            throw ProcessNotFoundException::fromExecutable($processInput->getExecutable());
        }

        return new Run(new Process(
            [$executablePath, ...$processInput->getArgs()],
            $processInput->getWorkingDirectory(),
            array_merge($this->getDefaultEnv(), $processInput->getEnv()),
            $processInput->getInput(),
            10,
        ));
    }

    /**
     * Symfony Process inherits the parent env, so we unset every inherited var by setting it to false.
     * We read from getenv() rather than $_ENV, as $_ENV is empty when variables_order does not include E.
     *
     * @return array<string, false>
     */
    private function getDefaultEnv(): array
    {
        $env = [];

        foreach (array_keys(getenv() + $_ENV) as $name) {
            if (!in_array($name, self::ALLOWED_ENV, true)) {
                $env[(string) $name] = false;
            }
        }

        return $env;
    }
}
