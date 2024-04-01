<?php

namespace PhpSchool\PhpWorkshop\ExerciseRunner\Context;

use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\Input\Input;
use PhpSchool\PhpWorkshop\Utils\Path;
use PhpSchool\PhpWorkshop\Utils\System;

class ExecutionContext
{
    /**
     * @var array<string, string>
     */
    private array $files = [];

    public Environment $studentEnvironment;
    public Environment $referenceEnvironment;


    public function __construct(
        string $studentWorkingDirectory,
        string $referenceWorkingDirectory,
        public ExerciseInterface $exercise,
        public Input $input,
    ) {
        $this->studentEnvironment = new Environment($this, $studentWorkingDirectory);
        $this->referenceEnvironment = new Environment($this, $referenceWorkingDirectory);
    }

    public static function fromInputAndExercise(Input $input, ExerciseInterface $exercise): self
    {
        return new self(
            dirname($input->getRequiredArgument('program')),
            System::randomTempDir(),
            $exercise,
            $input
        );
    }

    public function hasStudentSolution(): bool
    {
        return $this->input->hasArgument('program');
    }

    public function getStudentSolutionFilePath(): string
    {
        return Path::join(
            $this->studentEnvironment->workingDirectory,
            basename($this->input->getRequiredArgument('program'))
        );
    }

    public function addFile(string $relativeFileName, string $content): void
    {
        $this->files[$relativeFileName] = $content;
    }

    /**
     * @return array<string, string>
     */
    public function getFiles(): array
    {
        return $this->files;
    }
}
