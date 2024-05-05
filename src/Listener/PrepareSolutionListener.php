<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Listener;

use PhpSchool\PhpWorkshop\Event\ExerciseRunnerEvent;
use PhpSchool\PhpWorkshop\Exercise\ProvidesSolution;
use PhpSchool\PhpWorkshop\Exception\RuntimeException;
use PhpSchool\PhpWorkshop\Process\ProcessFactory;
use PhpSchool\PhpWorkshop\Utils\ArrayObject;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;
use PhpSchool\PhpWorkshop\Event\CliExecuteEvent;

/**
 * Listener to install composer deps for an exercise solution
 */
class PrepareSolutionListener
{
    private ProcessFactory $processFactory;

    public function __construct(ProcessFactory $processFactory)
    {
        $this->processFactory = $processFactory;
    }

    /**
     * @param ExerciseRunnerEvent $event
     */
    public function __invoke(ExerciseRunnerEvent $event): void
    {
        if (!$event->getExercise() instanceof ProvidesSolution) {
            return;
        }

        $solution = $event->getExercise()->getSolution();

        if (!$solution->hasComposerFile()) {
            return;
        }

        $this->runComposerInstallIn(
            $event->context->referenceExecutionDirectory
        );
    }

    private function runComposerInstallIn(string $directory): void
    {
        //prepare composer deps
        //only install if vendor folder not available

        if (!file_exists(sprintf('%s/vendor', $directory))) {
            $process = $this->processFactory->create(
                'composer',
                ['install', '--no-interaction'],
                $directory,
                []
            );

            try {
                $process->mustRun();
            } catch (\Symfony\Component\Process\Exception\RuntimeException $e) {
                throw new RuntimeException('Composer dependencies could not be installed', 0, $e);
            }
        }
    }
}
