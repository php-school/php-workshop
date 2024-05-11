<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Command;

use PhpSchool\PhpWorkshop\ExerciseDispatcher;
use PhpSchool\PhpWorkshop\ExerciseRepository;
use PhpSchool\PhpWorkshop\Input\Input;
use PhpSchool\PhpWorkshop\Output\OutputInterface;
use PhpSchool\PhpWorkshop\ResultRenderer\ResultsRenderer;
use PhpSchool\PhpWorkshop\UserState\UserState;
use PhpSchool\PhpWorkshop\UserState\Serializer;

/**
 * A command to verify the users solution
 */
class VerifyCommand
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
     * @var Serializer
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
     * @param Serializer $userStateSerializer
     * @param OutputInterface $output
     * @param ResultsRenderer $resultsRenderer
     */
    public function __construct(
        ExerciseRepository $exerciseRepository,
        ExerciseDispatcher $exerciseDispatcher,
        UserState $userState,
        Serializer $userStateSerializer,
        OutputInterface $output,
        ResultsRenderer $resultsRenderer,
    ) {
        $this->output = $output;
        $this->exerciseRepository = $exerciseRepository;
        $this->userState = $userState;
        $this->userStateSerializer = $userStateSerializer;
        $this->resultsRenderer = $resultsRenderer;
        $this->exerciseDispatcher = $exerciseDispatcher;
    }

    /**
     * @param Input $input The command line arguments passed to the command.
     *
     * @return int
     */
    public function __invoke(Input $input): int
    {
        $exercise   = $this->exerciseRepository->findByName($this->userState->getCurrentExercise());
        $results    = $this->exerciseDispatcher->verify($exercise, $input);

        if ($results->isSuccessful()) {
            $this->userState->addCompletedExercise($exercise->getName());
            $this->userStateSerializer->serialize($this->userState);
        }

        $this->resultsRenderer->render($results, $exercise, $this->userState, $this->output);
        return $results->isSuccessful() ? 0 : 1;
    }
}
