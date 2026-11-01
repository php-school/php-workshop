<?php

namespace PhpSchool\PhpWorkshop\Exercise\Scenario;

abstract class ExerciseScenario
{
    /**
     * @var array<string, string>
     */
    private array $files = [];

    private bool $exposesPort = false;

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

    /**
     * The code listens on a port, eg. a server the check connects to. The sandbox picks it, listeners get it from the
     * pre execute events with getWorkspace().
     */
    public function exposePort(): static
    {
        $this->exposesPort = true;

        return $this;
    }

    public function exposesPort(): bool
    {
        return $this->exposesPort;
    }
}
