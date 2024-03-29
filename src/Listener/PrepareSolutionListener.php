<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Listener;

use PhpSchool\PhpWorkshop\Event\ExerciseRunnerEvent;
use PhpSchool\PhpWorkshop\Exercise\ProvidesSolution;
use PhpSchool\PhpWorkshop\Exception\RuntimeException;
use Symfony\Component\Process\Process;

/**
 * Listener to install composer deps for an exercise solution
 */
class PrepareSolutionListener
{
    /**
     * Locations for composer executable
     *
     * @var array<string>
     */
    private static $composerLocations = [
        'composer',
        'composer.phar',
        '/usr/local/bin/composer',
        __DIR__ . '/../../vendor/bin/composer',
    ];

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

        if ($solution->hasComposerFile()) {
            //prepare composer deps
            //only install if composer.lock file not available

            if (!file_exists(sprintf('%s/vendor', $solution->getBaseDirectory()))) {
                $process = new Process(
                    [self::locateComposer(), 'install', '--no-interaction'],
                    $solution->getBaseDirectory()
                );

                try {
                    $process->mustRun();
                } catch (\Symfony\Component\Process\Exception\RuntimeException $e) {
                    throw new RuntimeException('Composer dependencies could not be installed', 0, $e);
                }
            }
        }
    }

    /**
     * @return string
     */
    public static function locateComposer(): string
    {
        foreach (self::$composerLocations as $location) {
            if (file_exists($location) && is_executable($location)) {
                return $location;
            }
        }

        throw new RuntimeException('Composer could not be located on the system');
    }
}
