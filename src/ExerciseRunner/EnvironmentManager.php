<?php

namespace PhpSchool\PhpWorkshop\ExerciseRunner;

use PhpSchool\PhpWorkshop\Exercise\ProvidesSolution;
use PhpSchool\PhpWorkshop\Exercise\Scenario\ExerciseScenario;
use PhpSchool\PhpWorkshop\ExerciseRunner\Context\ExecutionContext;
use PhpSchool\PhpWorkshop\Utils\Path;
use Symfony\Component\Filesystem\Filesystem;

class EnvironmentManager
{
    public function __construct(private Filesystem $filesystem)
    {
    }

    public function prepareStudent(ExecutionContext $context, ExerciseScenario $scenario): void
    {
        $this->copyExerciseFiles($scenario, $context->getStudentExecutionDirectory());
    }

    public function prepareSolution(ExecutionContext $context, ExerciseScenario $scenario): void
    {
        $exercise = $context->getExercise();

        if (!$exercise instanceof ProvidesSolution) {
            return;
        }

        $this->filesystem->mkdir($context->getReferenceExecutionDirectory());

        $solution = $exercise->getSolution();

        foreach ($solution->getFiles() as $file) {
            $this->filesystem->copy(
                $file->getAbsolutePath(),
                Path::join($context->getReferenceExecutionDirectory(), $file->getRelativePath())
            );
        }

        $this->copyExerciseFiles($scenario, $context->getReferenceExecutionDirectory());
    }

    private function copyExerciseFiles(ExerciseScenario $scenario, string $dir): void
    {
        foreach ($scenario->getFiles() as $fileName => $content) {
            $this->filesystem->dumpFile(
                Path::join($dir, $fileName),
                $content
            );
        }
    }

    public function cleanup(ExecutionContext $context, ExerciseScenario $scenario): void
    {
        $this->filesystem->remove($context->getReferenceExecutionDirectory());

        foreach ($scenario->getFiles() as $fileName => $content) {
            $this->filesystem->remove(Path::join($context->getStudentExecutionDirectory(), $fileName));
        }
    }
}
