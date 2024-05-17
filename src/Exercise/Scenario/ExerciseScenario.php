<?php

namespace PhpSchool\PhpWorkshop\Exercise\Scenario;

abstract class ExerciseScenario
{
    /**
     * @var array<string, string>
     */
    private array $files = [];

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
}
