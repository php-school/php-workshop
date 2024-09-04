<?php

namespace PhpSchool\PhpWorkshop\Process;

class ProcessInput
{
    /**
     * @param list<string> $args
     * @param array<string, string> $env
     * @param list<int> $exposedPorts
     */
    public function __construct(
        private string $executable,
        private array $args,
        private string $workingDirectory,
        private array $env,
        private array $exposedPorts,
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

    /**
     * @return list<int>
     */
    public function getExposedPorts(): array
    {
        return $this->exposedPorts;
    }

    public function getInput(): ?string
    {
        return $this->input;
    }
}
