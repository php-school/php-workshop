<?php

namespace PhpSchool\PhpWorkshop\Listener;

use PhpSchool\PhpWorkshop\CommandDefinition;
use PhpSchool\PhpWorkshop\Event\Event;
use PhpSchool\PhpWorkshop\Event\ExerciseRunnerEvent;
use PhpSchool\PhpWorkshop\Input\Input;
use PhpSchool\PhpWorkshop\UserState;

/**
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class RealPathListener
{

    /**
     * @param ExerciseRunnerEvent $event
     */
    public function __invoke(ExerciseRunnerEvent $event)
    {
        if (!$event->getInput()->hasArgument('program')) {
            return;
        }

        $program = $event->getInput()->getArgument('program');

        if (file_exists($program)) {
            $event->getInput()->setArgument('program', realpath($program));
        }
    }
}
