<?php

namespace PhpSchool\PhpWorkshop\ExerciseRunner\Context;

use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\Input\Input;
use PhpSchool\PhpWorkshop\Solution\SolutionInterface;
use PhpSchool\PhpWorkshop\Utils\System;
use PhpSchool\PhpWorkshopTest\Asset\CliExerciseImpl;
use Symfony\Component\Filesystem\Filesystem;
use PhpSchool\PhpWorkshop\Utils\Path;

class TestContext extends ExecutionContext
{
    public Filesystem $filesystem;
    public ExerciseInterface $exercise;

    private function __construct(
        ExerciseInterface $exercise = null,
        Input $input = null
    ) {
        $this->exercise = $exercise ?? new CliExerciseImpl();

        $this->filesystem = new Filesystem();

        parent::__construct(
            System::randomTempDir(),
            System::randomTempDir(),
            $this->exercise,
            $input ? $input : new Input('test', ['program' => 'solution.php']),
        );
    }

    public function importStudentSolution(string $file): void
    {
        copy($file, Path::join($this->studentExecutionDirectory, 'solution.php'));
    }

    public function importStudentSolutionFolder(string $folder): void
    {
        $this->filesystem->mirror($folder, $this->studentExecutionDirectory);
    }

    public function importReferenceSolution(SolutionInterface $solution): void
    {
        foreach ($solution->getFiles() as $file) {
            $this->filesystem->copy(
                $file->getAbsolutePath(),
                Path::join($this->referenceExecutionDirectory, $file->getRelativePath())
            );
        }
    }

    public static function withEnvironment(ExerciseInterface $exercise = null, Input $input = null): self
    {
        $self = new self($exercise, $input);

        $self->filesystem->mkdir($self->studentExecutionDirectory);
        $self->filesystem->mkdir($self->referenceExecutionDirectory);

        return $self;
    }

    public static function withoutEnvironment(ExerciseInterface $exercise = null, Input $input = null): self
    {
        return new self($exercise, $input);
    }

    public function __destruct()
    {
        $this->filesystem->remove($this->studentExecutionDirectory);
        $this->filesystem->remove($this->referenceExecutionDirectory);
    }
}
