<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Listener;

use PhpSchool\PhpWorkshop\Event\ExerciseRunnerEvent;
use PhpSchool\PhpWorkshop\Exercise\ProvidesSolution;
use PhpSchool\PhpWorkshop\Exception\RuntimeException;
use PhpSchool\PhpWorkshop\Process\ProcessFactory;
use PhpSchool\PhpWorkshop\Process\ProcessInput;

/**
 * Listener to install composer deps for an exercise solution
 */
class PrepareSolutionListener
{
    public function __construct(private ProcessFactory $processFactory)
    {
        $this->processFactory = $processFactory;
    }

    /**
     * @param ExerciseRunnerEvent $event
     */
    public function __invoke(ExerciseRunnerEvent $event): void
    {
        $exercise = $event->getExercise();

        if (!$exercise instanceof ProvidesSolution) {
            return;
        }

        $solution = $exercise->getSolution();

        if (!$solution->hasComposerFile()) {
            return;
        }

        //prepare composer deps
        //only install if vendor folder not available
        if (!file_exists(sprintf('%s/vendor', $event->getContext()->getReferenceExecutionDirectory()))) {
            $process = $this->processFactory->create(
                new ProcessInput('composer', ['install', '--no-interaction'], $event->getContext()->getReferenceExecutionDirectory(), []),
            );

            try {
                $process->mustRun();
            } catch (\Symfony\Component\Process\Exception\RuntimeException $e) {
                throw new RuntimeException('Composer dependencies could not be installed', 0, $e);
            }
        }
    }
}
