<?php

namespace PhpWorkshop\PhpWorkshop\Command;

use Colors\Color;
use Failure;
use MikeyMike\CliMenu\Terminal\TerminalFactory;
use MikeyMike\CliMenu\Terminal\UnixTerminal;
use PhpWorkshop\PhpWorkshop\ExerciseRepository;
use PhpWorkshop\PhpWorkshop\ExerciseRunner;
use PhpWorkshop\PhpWorkshop\Output;
use PhpWorkshop\PhpWorkshop\Result\StdOutFailure;
use PhpWorkshop\PhpWorkshop\Result\Success;
use PhpWorkshop\PhpWorkshop\ResultAggregator;
use PhpWorkshop\PhpWorkshop\ResultRenderer\FailureRenderer;
use PhpWorkshop\PhpWorkshop\ResultRenderer\ResultsRenderer;
use PhpWorkshop\PhpWorkshop\ResultRenderer\StdOutFailureRenderer;
use PhpWorkshop\PhpWorkshop\ResultRenderer\SuccessRenderer;
use PhpWorkshop\PhpWorkshop\UserState;

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
     * @param ExerciseRepository $exerciseRepository
     * @param ExerciseRunner $runner
     * @param UserState $userState
     * @param Output $output
     */
    public function __construct(
        ExerciseRepository $exerciseRepository,
        ExerciseRunner $runner,
        UserState $userState,
        Output $output
    ) {
        $this->runner               = $runner;
        $this->output               = $output;
        $this->exerciseRepository   = $exerciseRepository;
        $this->userState = $userState;
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

        $color = new Color;
        $color->setForceStyle(true);
        $terminal = new UnixTerminal;
        $width = $terminal->getWidth();

        echo $color(str_repeat("─", $width))->yellow() . "\n";

        $resultRenderer = new ResultsRenderer($color, (new TerminalFactory)->fromSystem(), $this->exerciseRepository);
        $resultRenderer->registerRenderer(StdOutFailure::class, new StdOutFailureRenderer($color));
        $resultRenderer->registerRenderer(Success::class, new SuccessRenderer);
        $resultRenderer->registerRenderer(Failure::class, new FailureRenderer);

        echo $resultRenderer->render($results, $exercise, $this->userState);

        //echo $resultRenderer->render($results);
    }
}
