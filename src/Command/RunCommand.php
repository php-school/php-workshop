<?php

namespace PhpSchool\PhpWorkshop\Command;

use PhpSchool\PhpWorkshop\ExerciseDispatcher;
use PhpSchool\PhpWorkshop\ExerciseRepository;
use PhpSchool\PhpWorkshop\Input\Input;
use PhpSchool\PhpWorkshop\Output\OutputInterface;
use PhpSchool\PhpWorkshop\UserState;
use PhpSchool\PhpWorkshop\UserStateSerializer;

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
     * @var ExerciseDispatcher
     */
    private $exerciseDispatcher;

    /**
     * @param ExerciseRepository $exerciseRepository
     * @param ExerciseDispatcher $exerciseDispatcher
     * @param UserState $userState
     * @param OutputInterface $output
     */
    public function __construct(
        ExerciseRepository $exerciseRepository,
        ExerciseDispatcher $exerciseDispatcher,
        UserState $userState,
        OutputInterface $output
    ) {
        $this->output               = $output;
        $this->exerciseRepository   = $exerciseRepository;
        $this->userState            = $userState;
        $this->exerciseDispatcher   = $exerciseDispatcher;
    }

    /**
     * @param Input $input The command line arguments passed to the command.
     *
     * @return int|void
     */
    public function __invoke(Input $input)
    {
        $exercise = $this->exerciseRepository->findByName($this->userState->getCurrentExercise());
        $this->exerciseDispatcher->run($exercise, $input, $this->output);
    }
}
