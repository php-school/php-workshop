<?php

namespace PhpSchool\PhpWorkshop\Listener;

use PhpSchool\PhpWorkshop\Event\ExerciseRunnerEvent;
use RuntimeException;
use Symfony\Component\Process\Process;

/**
 * Listener to install composer deps for an exercise solution
 */
class PrepareSolutionListener
{
    /**
     * Locations for composer executable
     *
     * @var array
     */
    private $composerLocations = [
        'composer',
        'composer.phar',
        '/usr/local/bin/composer',
        __DIR__ . '/../../vendor/bin/composer',
    ];

    /**
     * @param ExerciseRunnerEvent $event
     */
    public function __invoke(ExerciseRunnerEvent $event)
    {
        $solution = $event->getExercise()->getSolution();

        if ($solution->hasComposerFile()) {
            //prepare composer deps
            //only install if composer.lock file not available

            if (!file_exists(sprintf('%s/vendor', $solution->getBaseDirectory()))) {
                $process = new Process(
                    [$this->locateComposer(), 'install', '--no-interaction'],
                    $solution->getBaseDirectory()
                );
                $process->run();
            }
        }
    }

    /**
     * @return string
     */
    private function locateComposer()
    {
        foreach ($this->composerLocations as $location) {
            if (file_exists($location) && is_executable($location)) {
                return $location;
            }
        }

        throw new RuntimeException('Composer could not be located on the system');
    }
}
