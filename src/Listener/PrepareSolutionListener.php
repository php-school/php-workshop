<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Listener;

use PhpSchool\PhpWorkshop\Event\ExerciseRunnerEvent;
use PhpSchool\PhpWorkshop\Exercise\ProvidesSolution;
use PhpSchool\PhpWorkshop\Exception\RuntimeException;
use PhpSchool\PhpWorkshop\Process\Sandbox;
use PhpSchool\PhpWorkshop\Process\ProcessInput;

/**
 * Listener to install composer deps for an exercise solution, from the lock and cache written by ReferencePackages
 */
class PrepareSolutionListener
{
    public function __construct(private Sandbox $sandbox)
    {
        $this->sandbox = $sandbox;
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
            $execution = $this->sandbox->create(
                new ProcessInput(
                    'composer',
                    ['install', '--no-interaction'],
                    $event->getContext()->getReferenceExecutionDirectory(),
                    //everything is in the cache already, so a miss fails rather than downloading
                    ['COMPOSER_DISABLE_NETWORK' => '1'],
                    [],
                ),
            );

            try {
                $execution->mustRun();
            } catch (\Symfony\Component\Process\Exception\RuntimeException $e) {
                throw new RuntimeException('Composer dependencies could not be installed, has ReferencePackages been run?', 0, $e);
            }
        }
    }
}
