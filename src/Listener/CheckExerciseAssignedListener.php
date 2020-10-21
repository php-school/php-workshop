<?php

namespace PhpSchool\PhpWorkshop\Listener;

use PhpSchool\PhpWorkshop\CommandDefinition;
use PhpSchool\PhpWorkshop\Event\Event;
use PhpSchool\PhpWorkshop\UserState;

class CheckExerciseAssignedListener
{
    /**
     * @var UserState
     */
    private $userState;

    /**
     * @param UserState $userState
     */
    public function __construct(UserState $userState)
    {
        $this->userState = $userState;
    }

    /**
     * @param Event $event
     */
    public function __invoke(Event $event): void
    {
        /** @var CommandDefinition $command */
        $command = $event->getParameter('command');

        if (!in_array($command->getName(), ['verify', 'run', 'print'])) {
            return;
        }

        if (!$this->userState->isAssignedExercise()) {
            throw new \RuntimeException('No active exercise. Select one from the menu');
        }
    }
}
