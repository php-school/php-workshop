<?php

namespace PhpSchool\PhpWorkshop\Listener;

use PhpSchool\PhpWorkshop\Event\Event;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use RuntimeException;
use Symfony\Component\Process\Process;

/**
 * Class PrepareSolutionListener
 * @author Aydin Hassan <aydin@hotmail.co.uk>
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
     * @param Event $event
     */
    public function __invoke(Event $event)
    {
        $solution = $event->getParameter('exercise')->getSolution();

        if ($solution->hasComposerFile()) {
            //prepare composer deps
            //only install if composer.lock file not available

            if (!file_exists(sprintf('%s/vendor', $solution->getBaseDirectory()))) {
                $process = new Process(
                    sprintf('%s install --no-interaction', $this->locateComposer()),
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
