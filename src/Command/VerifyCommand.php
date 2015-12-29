<?php

namespace PhpSchool\PhpWorkshop\Command;

use PhpSchool\PhpWorkshop\ExerciseRepository;
use PhpSchool\PhpWorkshop\ExerciseRunner;
use PhpSchool\PhpWorkshop\Output;
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
     * @var Output
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
     * @param ExerciseRepository $exerciseRepository
     * @param ExerciseRunner $runner
     * @param UserState $userState
     * @param UserStateSerializer $userStateSerializer
     * @param Output $output
     * @param ResultsRenderer $resultsRenderer
     */
    public function __construct(
        ExerciseRepository $exerciseRepository,
        ExerciseRunner $runner,
        UserState $userState,
        UserStateSerializer $userStateSerializer,
        Output $output,
        ResultsRenderer $resultsRenderer
    ) {
        $this->runner               = $runner;
        $this->output               = $output;
        $this->exerciseRepository   = $exerciseRepository;
        $this->userState            = $userState;
        $this->userStateSerializer  = $userStateSerializer;
        $this->resultsRenderer          = $resultsRenderer;
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
        $results    = $this->runner->runExercise($exercise, $program);

        if ($results->isSuccessful()) {
            $this->userState->addCompletedExercise($exercise->getName());
            $this->userStateSerializer->serialize($this->userState);
        }
        
        $this->resultsRenderer->render($results, $exercise, $this->userState, $this->output);
        return $results->isSuccessful() ? 0 : 1;
    }
}
