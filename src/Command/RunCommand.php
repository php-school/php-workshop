<?php

namespace PhpSchool\PhpWorkshop\Command;

use PhpSchool\PhpWorkshop\ExerciseDispatcher;
use PhpSchool\PhpWorkshop\ExerciseRepository;
use PhpSchool\PhpWorkshop\Output\OutputInterface;
use PhpSchool\PhpWorkshop\UserState;
use PhpSchool\PhpWorkshop\UserStateSerializer;

/**
 * Class RunCommand
 * @package PhpSchool\PhpWorkshop\Command
 * @author Michael Woodward <mikeymike.mw@gmail.com>
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class RunCommand
{
    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var ExerciseRepository
     */
    private $exerciseRepository;

    /**
     * @var UserState
     */
    private $userState;

    /**
     * @var UserStateSerializer
     */
    private $userStateSerializer;

    /**
     * @var ExerciseDispatcher
     */
    private $exerciseDispatcher;

    /**
     * @param ExerciseRepository $exerciseRepository
     * @param ExerciseDispatcher $exerciseDispatcher
     * @param UserState $userState
     * @param UserStateSerializer $userStateSerializer
     * @param OutputInterface $output
     */
    public function __construct(
        ExerciseRepository $exerciseRepository,
        ExerciseDispatcher $exerciseDispatcher,
        UserState $userState,
        UserStateSerializer $userStateSerializer,
        OutputInterface $output
    ) {
        $this->output               = $output;
        $this->exerciseRepository   = $exerciseRepository;
        $this->userState            = $userState;
        $this->userStateSerializer  = $userStateSerializer;
        $this->exerciseDispatcher   = $exerciseDispatcher;
    }

    /**
     * @param string $appName
     * @param string $program
     *
     * @return int|void
     */
    public function __invoke($appName, $program = null)
    {
        if (null != $program && file_exists($program)) {
//            $this->output->printError(
//                sprintf('Could not run. File: "%s" does not exist', $program)
//            );
//            return 1;
            $program = realpath($program);
        }

        if (!$this->userState->isAssignedExercise()) {
            $this->output->printError("No active exercises. Select one from the menu");
            return 1;
        }

        $exercise = $this->exerciseRepository->findByName($this->userState->getCurrentExercise());

        $this->exerciseDispatcher->run($exercise, $program, $this->output);
    }
}
