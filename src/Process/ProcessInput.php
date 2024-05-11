<?php

namespace PhpSchool\PhpWorkshop\Process;

class ProcessInput
{
    /**
     * @param list<string> $args
     * @param array<string, string> $env
     */
    public function __construct(
        private string $executable,
        private array $args,
        private string $workingDirectory,
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

    public function getWorkingDirectory(): string
    {
        return $this->workingDirectory;
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
