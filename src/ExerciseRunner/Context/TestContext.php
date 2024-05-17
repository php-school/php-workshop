<?php

namespace PhpSchool\PhpWorkshop\ExerciseRunner\Context;

use PhpSchool\PhpWorkshop\Exception\RuntimeException;
use PhpSchool\PhpWorkshop\Exercise\CgiExercise;
use PhpSchool\PhpWorkshop\Exercise\CliExercise;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\Exercise\MockExercise;
use PhpSchool\PhpWorkshop\Input\Input;
use PhpSchool\PhpWorkshop\Solution\SolutionInterface;
use PhpSchool\PhpWorkshop\Utils\System;
use Symfony\Component\Filesystem\Filesystem;
use PhpSchool\PhpWorkshop\Utils\Path;

class TestContext extends ExecutionContext
{
    private Filesystem $filesystem;
    private ExerciseInterface $exercise;
    private bool $studentSolutionDirWasCreated = false;
    private bool $referenceSolutionDirWasCreated = false;

    public function __construct(
        ExerciseInterface $exercise = null,
        Input $input = null,
        string $studentDirectory = null,
    ) {
        $this->exercise = $exercise ?? new MockExercise();

        $this->filesystem = new Filesystem();

        if ($studentDirectory === null) {
            $studentDirectory = System::randomTempDir();
        }

        parent::__construct(
            $studentDirectory,
            System::randomTempDir(),
            $this->exercise,
            $input ? $input : new Input('test', ['program' => 'solution.php']),
        );
    }

    public function createStudentSolutionDirectory(): void
    {
        $this->filesystem->mkdir($this->getStudentExecutionDirectory());
        $this->studentSolutionDirWasCreated = true;
    }

    public function createReferenceSolutionDirectory(): void
    {
        $this->filesystem->mkdir($this->getReferenceExecutionDirectory());
        $this->referenceSolutionDirWasCreated = true;
    }

    public function importStudentFileFromString(string $content, string $filename = 'solution.php'): void
    {
        if (!$this->studentSolutionDirWasCreated) {
            throw new RuntimeException(
                sprintf('Student execution directory not created. Call %s::createStudentSolutionDirectory() first.', self::class)
            );
        }

        file_put_contents(Path::join($this->getStudentExecutionDirectory(), $filename), $content);
    }

    public function importReferenceFileFromString(string $content, string $filename = 'solution.php'): void
    {
        if (!$this->referenceSolutionDirWasCreated) {
            throw new RuntimeException(
                sprintf('Reference execution directory not created. Call %s::createReferenceSolutionDirectory() first.', self::class)
            );
        }

        file_put_contents(Path::join($this->getReferenceExecutionDirectory(), $filename), $content);
    }

    public static function fromExerciseAndStudentSolution(ExerciseInterface $exercise, string $file): self
    {
        if (file_exists($file)) {
            $file = (string) realpath($file);
        }

        $input = new Input('test', ['program' => $file]);
        return new self(
            exercise: $exercise,
            input: $input,
            studentDirectory: dirname($file)
        );
    }

    public function __destruct()
    {
        if ($this->studentSolutionDirWasCreated) {
            $this->filesystem->remove($this->getStudentExecutionDirectory());
        }

        if ($this->referenceSolutionDirWasCreated) {
            $this->filesystem->remove($this->getReferenceExecutionDirectory());
        }
    }
}
