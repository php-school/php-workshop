<?php

namespace PhpSchool\PhpWorkshop\Process\Sandbox;

/**
 * The command to run in a sandbox, eg. `php solution.php 0.0.0.0 54321`.
 */
class ProcessInput
{
    /**
     * @param string $executable eg. php or php-cgi
     * @param list<string> $args eg. ['solution.php', '0.0.0.0', '54321']
     * @param array<string, string> $env extra env vars, eg. the request for php-cgi
     * @param string|null $input written to stdin, eg. the request body for php-cgi
     */
    public function __construct(
        private string $executable,
        private array $args,
        private array $env,
        private ?string $input = null,
    ) {}

    public function getExecutable(): string
    {
        return $this->executable;
    }

    /**
     * @return list<string>
     */
    public function getArgs(): array
    {
        return $this->args;
    }

    /**
     * @return array<string, string>
     */
    public function getEnv(): array
    {
        return $this->env;
    }

    public function getInput(): ?string
    {
        return $this->input;
    }
}
