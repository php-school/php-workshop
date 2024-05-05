<?php

namespace PhpSchool\PhpWorkshop\ExerciseRunner\Context;

use PhpSchool\PhpWorkshop\Exception\RuntimeException;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\Exercise\MockExercise;
use PhpSchool\PhpWorkshop\Input\Input;
use PhpSchool\PhpWorkshop\Solution\SolutionInterface;
use PhpSchool\PhpWorkshop\Utils\System;
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
        $this->exercise = $exercise ?? new MockExercise();

        $this->filesystem = new Filesystem();

        parent::__construct(
            System::randomTempDir(),
            System::randomTempDir(),
            $this->exercise,
            $input ? $input : new Input('test', ['program' => 'solution.php']),
        );
    }

    public function importStudentFileFromString(string $content, string $filename = 'solution.php'): void
    {
        if (!$this->filesystem->exists($this->getStudentExecutionDirectory())) {
            throw new RuntimeException(
                sprintf('Execution directories not created. Use %s::withDirectories() method instead.', self::class)
            );
        }

        file_put_contents(Path::join($this->getStudentExecutionDirectory(), $filename), $content);
    }

    public function importStudentSolution(string $file): void
    {
        if (!$this->filesystem->exists($this->getStudentExecutionDirectory())) {
            throw new RuntimeException(
                sprintf('Execution directories not created. Use %s::withDirectories() method instead.', self::class)
            );
        }

        copy($file, Path::join($this->getStudentExecutionDirectory(), 'solution.php'));
    }

    public function importStudentSolutionFolder(string $folder): void
    {
        if (!$this->filesystem->exists($this->getStudentExecutionDirectory())) {
            throw new RuntimeException(
                sprintf('Execution directories not created. Use %s::withDirectories() method instead.', self::class)
            );
        }

        $this->filesystem->mirror($folder, $this->getStudentExecutionDirectory());
    }

    public function importReferenceFileFromString(string $content, string $filename = 'solution.php'): void
    {
        if (!$this->filesystem->exists($this->getReferenceExecutionDirectory())) {
            throw new RuntimeException(
                sprintf('Execution directories not created. Use %s::withDirectories() method instead.', self::class)
            );
        }

        file_put_contents(Path::join($this->getReferenceExecutionDirectory(), $filename), $content);
    }

    public function importReferenceSolution(SolutionInterface $solution): void
    {
        if (!$this->filesystem->exists($this->getReferenceExecutionDirectory())) {
            throw new RuntimeException(
                sprintf('Execution directories not created. Use %s::withDirectories() method instead.', self::class)
            );
        }

        foreach ($solution->getFiles() as $file) {
            $this->filesystem->copy(
                $file->getAbsolutePath(),
                Path::join($this->getReferenceExecutionDirectory(), $file->getRelativePath())
            );
        }
    }

    public static function withDirectories(Input $input = null, ExerciseInterface $exercise = null): self
    {
        $self = new self($exercise, $input);

        $self->filesystem->mkdir($self->getStudentExecutionDirectory());
        $self->filesystem->mkdir($self->getReferenceExecutionDirectory());

        return $self;
    }

    public static function withoutDirectories(Input $input = null, ExerciseInterface $exercise = null): self
    {
        return new self($exercise, $input);
    }

    public function __destruct()
    {
        $this->filesystem->remove($this->getStudentExecutionDirectory());
        $this->filesystem->remove($this->getReferenceExecutionDirectory());
    }
}
