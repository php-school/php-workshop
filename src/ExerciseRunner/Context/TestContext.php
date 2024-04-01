<?php

namespace PhpSchool\PhpWorkshop\ExerciseRunner\Context;

use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\Input\Input;
use PhpSchool\PhpWorkshop\Utils\System;
use PhpSchool\PhpWorkshopTest\Asset\CliExerciseImpl;
use Symfony\Component\Filesystem\Filesystem;
use PhpSchool\PhpWorkshop\Utils\Path;

class TestContext implements RunnerContext
{
    private ExecutionContext $executionContext;
    public string $studentWorkingDirectory;
    public string $referenceWorkingDirectory;
    public Filesystem $filesystem;
    public ExerciseInterface $exercise;

    private function __construct(
        ExerciseInterface $exercise = null,
        Input $input = null
    ) {
        $this->exercise = $exercise ?? new CliExerciseImpl();

        $this->filesystem = new Filesystem();

        $this->studentWorkingDirectory = System::randomTempDir();
        $this->referenceWorkingDirectory = System::randomTempDir();

        $this->executionContext = new ExecutionContext(
            $this->studentWorkingDirectory,
            $this->referenceWorkingDirectory,
            $this->exercise,
            $input ? $input : new Input('test', ['program' => 'solution.php']),
        );
    }

    public function importSolution(string $file): void
    {
        copy($file, Path::join($this->studentWorkingDirectory, 'solution.php'));
    }

    public static function withEnvironment(ExerciseInterface $exercise = null, Input $input = null): self
    {
        $self = new self($exercise, $input);

        $self->filesystem->mkdir($self->studentWorkingDirectory);
        $self->filesystem->mkdir($self->referenceWorkingDirectory);

        return $self;
    }

    public static function withoutEnvironment(ExerciseInterface $exercise = null, Input $input = null): self
    {
        return new self($exercise, $input);
    }

    public function getExecutionContext(): ExecutionContext
    {
        return $this->executionContext;
    }

    public function __destruct()
    {
        $this->filesystem->remove($this->studentWorkingDirectory);
        $this->filesystem->remove($this->referenceWorkingDirectory);
    }
}
