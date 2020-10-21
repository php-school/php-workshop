<?php

namespace PhpSchool\PhpWorkshop\Listener;

use PhpSchool\PhpWorkshop\CommandDefinition;
use PhpSchool\PhpWorkshop\Event\Event;
use PhpSchool\PhpWorkshop\Event\ExerciseRunnerEvent;
use PhpSchool\PhpWorkshop\Input\Input;
use PhpSchool\PhpWorkshop\UserState;

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
