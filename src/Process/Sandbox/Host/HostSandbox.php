<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Process\Sandbox\Host;

use PhpSchool\PhpWorkshop\ExerciseRunner\Context\ExecutionContext;
use PhpSchool\PhpWorkshop\Process\ProcessNotFoundException;
use PhpSchool\PhpWorkshop\Process\Sandbox\ProcessInput;
use PhpSchool\PhpWorkshop\Process\Sandbox\Sandbox;
use PhpSchool\PhpWorkshop\Process\Sandbox\SandboxExecution;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

/**
 * Runs the code straight on this machine, in the solution's directory.
 */
final class HostSandbox implements Sandbox
{
    /**
     * Inherited env vars that are kept, everything else is unset so we don't leak secrets to the process
     */
    private const ALLOWED_ENV = ['PATH', 'HOME', 'COMPOSER_HOME'];

    public function __construct(private string $directory, private ?int $port, private ExecutableFinder $executableFinder) {}

    public function execute(ProcessInput $processInput): SandboxExecution
    {
        $executablePath = $this->executableFinder->find($processInput->getExecutable());

        if ($executablePath === null) {
            throw ProcessNotFoundException::fromExecutable($processInput->getExecutable());
        }

        return new SandboxExecution(new Process(
            [$executablePath, ...$processInput->getArgs()],
            $this->directory,
            array_merge($this->getDefaultEnv(), $processInput->getEnv()),
            $processInput->getInput(),
            10,
        ), '127.0.0.1', $this->port);
    }

    public function getPort(): int
    {
        return $this->port ?? throw new \RuntimeException('The scenario does not expose a port');
    }

    /**
     * The code ran in the directory, so the solution files are there already.
     */
    public function finish(ExecutionContext $context): void {}

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
