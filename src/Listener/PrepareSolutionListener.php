<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Listener;

use PhpSchool\PhpWorkshop\Event\ExerciseRunnerEvent;
use PhpSchool\PhpWorkshop\Exercise\ProvidesSolution;
use PhpSchool\PhpWorkshop\Exception\RuntimeException;
use PhpSchool\PhpWorkshop\Process\Sandbox;
use PhpSchool\PhpWorkshop\Process\ProcessInput;
use PhpSchool\PhpWorkshop\Process\SandboxExecution;

/**
 * Listener to install composer deps for an exercise solution, offline when ReferencePackages has warmed the cache
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
        if (file_exists(sprintf('%s/vendor', $event->getContext()->getReferenceExecutionDirectory()))) {
            return;
        }

        //offline first, from the cache ReferencePackages warms. Without it, eg. a workshop run from a clone, download
        $offline = $this->install($event, ['COMPOSER_DISABLE_NETWORK' => '1']);
        $offline->start();
        $offline->wait();

        if ($offline->isSuccessful()) {
            return;
        }

        try {
            $this->install($event, [])->mustRun();
        } catch (\Symfony\Component\Process\Exception\RuntimeException $e) {
            throw new RuntimeException('Composer dependencies could not be installed', 0, $e);
        }
    }

    /**
     * @param array<string, string> $env
     */
    private function install(ExerciseRunnerEvent $event, array $env): SandboxExecution
    {
        return $this->sandbox->create(
            new ProcessInput('composer', ['install', '--no-interaction'], $event->getContext()->getReferenceExecutionDirectory(), $env, []),
        );
    }
}
