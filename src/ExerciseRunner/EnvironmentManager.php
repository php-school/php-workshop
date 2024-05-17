<?php

namespace PhpSchool\PhpWorkshop\ExerciseRunner;

use PhpSchool\PhpWorkshop\Event\EventDispatcher;
use PhpSchool\PhpWorkshop\Exercise\ProvidesSolution;
use PhpSchool\PhpWorkshop\Exercise\Scenario\ExerciseScenario;
use PhpSchool\PhpWorkshop\ExerciseRunner\Context\ExecutionContext;
use PhpSchool\PhpWorkshop\Utils\Path;
use Symfony\Component\Filesystem\Filesystem;

class EnvironmentManager
{
    public function __construct(private Filesystem $filesystem, private EventDispatcher $eventDispatcher) {}

    public function prepareStudent(ExecutionContext $context, ExerciseScenario $scenario): void
    {
        $this->copyExerciseFiles($scenario, $context->getStudentExecutionDirectory());

        //cleanup the files when the run or verification process is finished
        //we do this at late as possible in case any checks or other event listeners need to access the files
        $this->eventDispatcher->listen(['run.finish', 'verify.finish'], function () use ($context, $scenario) {
            foreach ($scenario->getFiles() as $fileName => $content) {
                $this->filesystem->remove(Path::join($context->getStudentExecutionDirectory(), $fileName));
            }
        });
    }

    public function prepareReference(ExecutionContext $context, ExerciseScenario $scenario): void
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
                Path::join($context->getReferenceExecutionDirectory(), $file->getRelativePath()),
            );
        }

        $this->copyExerciseFiles($scenario, $context->getReferenceExecutionDirectory());

        //cleanup the files when the run or verification process is finished
        //we do this at late as possible in case any checks or other event listeners need to access the files
        $this->eventDispatcher->listen(['run.finish', 'verify.finish'], function () use ($context) {
            $this->filesystem->remove($context->getReferenceExecutionDirectory());
        });
    }

    private function copyExerciseFiles(ExerciseScenario $scenario, string $dir): void
    {
        foreach ($scenario->getFiles() as $fileName => $content) {
            $this->filesystem->dumpFile(
                Path::join($dir, $fileName),
                $content,
            );
        }
    }
}
