<?php

namespace PhpWorkshop\PhpWorkshop\Command;

use Colors\Color;
use Failure;
use MikeyMike\CliMenu\Terminal\TerminalFactory;
use MikeyMike\CliMenu\Terminal\UnixTerminal;
use PhpWorkshop\PhpWorkshop\ExerciseRepository;
use PhpWorkshop\PhpWorkshop\ExerciseRunner;
use PhpWorkshop\PhpWorkshop\Output;
use PhpWorkshop\PhpWorkshop\Result\FunctionRequirementsFailure;
use PhpWorkshop\PhpWorkshop\Result\StdOutFailure;
use PhpWorkshop\PhpWorkshop\Result\Success;
use PhpWorkshop\PhpWorkshop\ResultAggregator;
use PhpWorkshop\PhpWorkshop\ResultRenderer\FailureRenderer;
use PhpWorkshop\PhpWorkshop\ResultRenderer\FunctionRequirementsFailureRenderer;
use PhpWorkshop\PhpWorkshop\ResultRenderer\ResultsRenderer;
use PhpWorkshop\PhpWorkshop\ResultRenderer\StdOutFailureRenderer;
use PhpWorkshop\PhpWorkshop\ResultRenderer\SuccessRenderer;
use PhpWorkshop\PhpWorkshop\UserState;
use PhpWorkshop\PhpWorkshop\UserStateSerializer;

/**
 * Class VerifyCommand
 * @package PhpWorkshop\PhpWorkshop\Command
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class VerifyCommand
{
    /**
     * @var \PhpWorkshop\PhpWorkshop\ExerciseRunner
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
        
        $this->output->write($this->resultsRenderer->render($results, $exercise, $this->userState));
        return $results->isSuccessful() ? 0 : 1;
    }
}
