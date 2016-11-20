<?php

namespace PhpSchool\PhpWorkshop\Listener;

use PhpSchool\PhpWorkshop\CommandDefinition;
use PhpSchool\PhpWorkshop\Event\Event;
use PhpSchool\PhpWorkshop\ExerciseRepository;
use PhpSchool\PhpWorkshop\ExerciseRunner\RunnerManager;
use PhpSchool\PhpWorkshop\UserState;

/**
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class ConfigureCommandListener
{
    /**
     * @var ExerciseRepository
     */
    private $exerciseRepository;

    /**
     * @var RunnerManager
     */
    private $runnerManager;

    /**
     * @param UserState $userState
     * @param ExerciseRepository $exerciseRepository
     * @param RunnerManager $runnerManager
     */
    public function __construct(
        UserState $userState,
        ExerciseRepository $exerciseRepository,
        RunnerManager $runnerManager
    ) {
        $this->userState = $userState;
        $this->exerciseRepository = $exerciseRepository;
        $this->runnerManager = $runnerManager;
    }

    /**
     * @param Event $event
     */
    public function __invoke(Event $event)
    {
        /** @var CommandDefinition $command */
        $command = $event->getParameter('command');

        if (!in_array($command->getName(), ['verify', 'run'])) {
            return;
        }

        $currentExercise = $this->exerciseRepository->findByName(
            $this->userState->getCurrentExercise()
        );

        $this->runnerManager->configureInput($currentExercise, $command);
    }
}
