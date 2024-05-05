<?php

namespace PhpSchool\PhpWorkshop\Listener;

use PhpSchool\PhpWorkshop\Event\CgiExerciseRunnerEvent;
use PhpSchool\PhpWorkshop\Event\CliExerciseRunnerEvent;
use PhpSchool\PhpWorkshop\Event\ExerciseRunnerEvent;
use PhpSchool\PhpWorkshop\Exercise\ProvidesSolution;
use PhpSchool\PhpWorkshop\Utils\Path;
use Symfony\Component\Filesystem\Filesystem;

class PrepareReferenceEnvironment
{
    public function __construct(private Filesystem $filesystem)
    {
    }

    public function __invoke(CliExerciseRunnerEvent|CgiExerciseRunnerEvent $event): void
    {
        if (!$event->getExercise() instanceof ProvidesSolution) {
            return;
        }

        $solution = $event->getExercise()->getSolution();
        $context  = $event->context;

        foreach ($solution->getFiles() as $file) {
            $this->filesystem->copy(
                $file->getAbsolutePath(),
                Path::join($context->referenceExecutionDirectory, $file->getRelativePath())
            );
        }

        foreach ($event->environment->files as $fileName => $content) {
            file_put_contents(
                Path::join($context->referenceExecutionDirectory, $fileName),
                $content
            );
        }

//        sleep(1);
    }
}
