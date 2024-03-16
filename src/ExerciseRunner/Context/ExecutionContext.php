<?php

namespace PhpSchool\PhpWorkshop\ExerciseRunner\Context;

use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\Input\Input;
use PhpSchool\PhpWorkshop\Utils\System;

class ExecutionContext
{
    private array $files = [];

    public Environment $studentEnvironment;
    public Environment $referenceEnvironment;

    private function __construct(
        string $studentWorkingDirectory,
        string $referenceWorkingDirectory,
        public Input $input,
        public ExerciseInterface $exercise,
    ) {
        $this->studentEnvironment = new Environment($this, $studentWorkingDirectory);
        $this->referenceEnvironment = new Environment($this, $referenceWorkingDirectory);
    }

    public static function fromInputAndExercise(Input $input, ExerciseInterface $exercise): self
    {
        return new static(
            dirname($input->getRequiredArgument('program')),
            System::randomTempDir(),
            $input,
            $exercise,
        );
    }

    public function addFile(string $relativeFileName, string $content): void
    {
        $this->files[$relativeFileName] = $content;
    }

    public function getFiles(): array
    {
        return $this->files;
    }
}