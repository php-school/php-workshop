<?php

namespace PhpSchool\PhpWorkshop\ExerciseRunner\Context;

use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\Input\Input;
use PhpSchool\PhpWorkshop\Utils\Path;
use PhpSchool\PhpWorkshop\Utils\System;

class ExecutionContext
{
    public function __construct(
        private string $studentExecutionDirectory,
        private string $referenceExecutionDirectory,
        private ExerciseInterface $exercise,
        private Input $input,
        private bool $readsSolutionFiles = false,
    ) {}

    /**
     * @param bool $readsSolutionFiles a check reads the files the solutions wrote, see ReadsSolutionFiles
     */
    public static function fromInputAndExercise(Input $input, ExerciseInterface $exercise, bool $readsSolutionFiles = false): ExecutionContext
    {
        $program = $input->hasArgument('program') ? dirname($input->getRequiredArgument('program')) : (string) getcwd();

        return new self(
            $program,
            System::randomTempDir(),
            $exercise,
            $input,
            $readsSolutionFiles,
        );
    }

    public function getExercise(): ExerciseInterface
    {
        return $this->exercise;
    }

    public function getInput(): Input
    {
        return $this->input;
    }

    public function hasStudentSolution(): bool
    {
        return $this->input->hasArgument('program');
    }

    public function getEntryPoint(): string
    {
        if (!$this->hasStudentSolution()) {
            throw new NoEntryPoint();
        }

        return Path::join(
            $this->studentExecutionDirectory,
            basename($this->input->getRequiredArgument('program')),
        );
    }

    public function getStudentExecutionDirectory(): string
    {
        return $this->studentExecutionDirectory;
    }

    public function getReferenceExecutionDirectory(): string
    {
        return $this->referenceExecutionDirectory;
    }

    /**
     * A check reads the files the solutions wrote, so the sandbox must copy them back.
     */
    public function readsSolutionFiles(): bool
    {
        return $this->readsSolutionFiles;
    }
}
