<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Listener;

use PhpSchool\PhpWorkshop\CommandDefinition;
use PhpSchool\PhpWorkshop\Event\Event;
use PhpSchool\PhpWorkshop\ExerciseRepository;
use PhpSchool\PhpWorkshop\ExerciseRunner\RunnerManager;
use PhpSchool\PhpWorkshop\UserState;

/**
 * Listener to allow runners to modify command arguments
 */
class ConfigureCommandListener
{
    /**
     * @var UserState
     */
    private $userState;

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
    public function __invoke(Event $event): void
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
