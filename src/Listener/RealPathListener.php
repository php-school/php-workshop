<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Listener;

use PhpSchool\PhpWorkshop\Event\ExerciseRunnerEvent;

/**
 * Replace program arg with absolute path
 */
class RealPathListener
{
    /**
     * @param ExerciseRunnerEvent $event
     */
    public function __invoke(ExerciseRunnerEvent $event): void
    {
        if (!$event->getInput()->hasArgument('program')) {
            return;
        }

        $program = $event->getInput()->getRequiredArgument('program');

        if (file_exists($program)) {
            $event->getInput()->setArgument('program', (string) realpath($program));
        }
    }
}
