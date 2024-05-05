<?php

namespace PhpSchool\PhpWorkshop\ExerciseRunner\Context;

use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\Input\Input;
use PhpSchool\PhpWorkshop\Utils\Path;
use PhpSchool\PhpWorkshop\Utils\System;

class ExecutionContext
{
    public string $studentExecutionDirectory;
    public string $referenceExecutionDirectory;

    public function __construct(
        string $studentWorkingDirectory,
        string $referenceWorkingDirectory,
        public ExerciseInterface $exercise,
        public Input $input,
    ) {
        $this->studentExecutionDirectory = $studentWorkingDirectory;
        $this->referenceExecutionDirectory = $referenceWorkingDirectory;
    }

    public function hasStudentSolution(): bool
    {
        return $this->input->hasArgument('program');
    }

    public function getEntryPoint(): string
    {
        return Path::join(
            $this->studentExecutionDirectory,
            basename($this->input->getRequiredArgument('program'))
        );
    }
}
