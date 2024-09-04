<?php

namespace PhpSchool\PhpWorkshop\Exercise\Scenario;

abstract class ExerciseScenario
{
    /**
     * @var array<string, string>
     */
    private array $files = [];

    /**
     * @var list<int>
     */
    private array $exposedPorts = [];

    public function withFile(string $relativeFileName, string $content): static
    {
        $this->files[$relativeFileName] = $content;

        return $this;
    }

    /**
     * @return array<string, string>
     */
    public function getFiles(): array
    {
        return $this->files;
    }

    public function exposePort(int $port): static
    {
        $this->exposedPorts = [$port];

        return $this;
    }

    /**
     * @return list<int>
     */
    public function getExposedPorts(): array
    {
        return $this->exposedPorts;
    }
}
