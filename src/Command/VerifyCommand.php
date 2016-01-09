<?php

namespace PhpSchool\PhpWorkshop\Command;

use PhpSchool\PhpWorkshop\ExerciseDispatcher;
use PhpSchool\PhpWorkshop\ExerciseRepository;
use PhpSchool\PhpWorkshop\ExerciseRunner;
use PhpSchool\PhpWorkshop\Output\OutputInterface;
use PhpSchool\PhpWorkshop\ResultRenderer\ResultsRenderer;
use PhpSchool\PhpWorkshop\UserState;
use PhpSchool\PhpWorkshop\UserStateSerializer;

/**
 * Class VerifyCommand
 * @package PhpSchool\PhpWorkshop\Command
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class VerifyCommand
{
    /**
     * @var \PhpSchool\PhpWorkshop\ExerciseRunner
     */
    private $runner;

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
     * @var ResultsRenderer
     */
    private $resultsRenderer;

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
     * @param ResultsRenderer $resultsRenderer
     */
    public function __construct(
        ExerciseRepository $exerciseRepository,
        ExerciseDispatcher $exerciseDispatcher,
        UserState $userState,
        UserStateSerializer $userStateSerializer,
        OutputInterface $output,
        ResultsRenderer $resultsRenderer
    ) {
        $this->output               = $output;
        $this->exerciseRepository   = $exerciseRepository;
        $this->userState            = $userState;
        $this->userStateSerializer  = $userStateSerializer;
        $this->resultsRenderer      = $resultsRenderer;
        $this->exerciseDispatcher   = $exerciseDispatcher;
    }

    /**
     * @param string $appName
     * @param string $program
     *
     * @return int|void
     */
    public function __invoke($appName, $program)
    {
        if (!file_exists($program)) {
            $this->output->printError(
                sprintf('Could not verify. File: "%s" does not exist', $program)
            );
            return 1;
        }
        $program = realpath($program);

        if (!$this->userState->isAssignedExercise()) {
            $this->output->printError("No active exercises. Select one from the menu");
            return 1;
        }

        $exercise   = $this->exerciseRepository->findByName($this->userState->getCurrentExercise());
        $results    = $this->exerciseDispatcher->verify($exercise, $program);

        if ($results->isSuccessful()) {
            $this->userState->addCompletedExercise($exercise->getName());
            $this->userStateSerializer->serialize($this->userState);
        }
        
        $this->resultsRenderer->render($results, $exercise, $this->userState, $this->output);
        return $results->isSuccessful() ? 0 : 1;
    }
}
