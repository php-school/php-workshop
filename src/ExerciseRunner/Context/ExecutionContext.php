<?php

namespace PhpSchool\PhpWorkshop\ExerciseRunner\Context;

use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\Input\Input;
use PhpSchool\PhpWorkshop\Utils\Path;
use PhpSchool\PhpWorkshop\Utils\System;

class ExecutionContext
{
    /**
     * @param list<string> $solutionFiles see getSolutionFiles()
     */
    public function __construct(
        private string $studentExecutionDirectory,
        private string $referenceExecutionDirectory,
        private ExerciseInterface $exercise,
        private Input $input,
        private array $solutionFiles = [],
    ) {}

    /**
     * @param list<string> $solutionFiles see getSolutionFiles()
     */
    public static function fromInputAndExercise(Input $input, ExerciseInterface $exercise, array $solutionFiles = []): ExecutionContext
    {
        $program = $input->hasArgument('program') ? dirname($input->getRequiredArgument('program')) : (string) getcwd();

        return new self(
            $program,
            System::randomTempDir(),
            $exercise,
            $input,
            $solutionFiles,
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
     * The files the checks read from the solutions' directories after they ran, see ReadsSolutionFiles.
     *
     * @return list<string> eg. ['db.sqlite']
     */
    public function getSolutionFiles(): array
    {
        return $this->solutionFiles;
    }
}
