<?php

namespace PhpSchool\PhpWorkshop\Command;

use PhpSchool\PhpWorkshop\ExerciseRepository;
use PhpSchool\PhpWorkshop\MarkdownRenderer;
use PhpSchool\PhpWorkshop\Output\OutputInterface;
use PhpSchool\PhpWorkshop\UserState;

/**
 * Class PrintCommand
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class PrintCommand
{
    /**
     * @var MarkdownRenderer
     */
    private $markdownRenderer;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var UserState
     */
    private $userState;

    /**
     * @var ExerciseRepository
     */
    private $exerciseRepository;

    /**
     * @param ExerciseRepository $exerciseRepository
     * @param UserState $userState
     * @param MarkdownRenderer $markdownRenderer
     * @param OutputInterface $output
     */
    public function __construct(
        ExerciseRepository $exerciseRepository,
        UserState $userState,
        MarkdownRenderer $markdownRenderer,
        OutputInterface $output
    ) {
        $this->markdownRenderer     = $markdownRenderer;
        $this->output               = $output;
        $this->userState            = $userState;
        $this->exerciseRepository   = $exerciseRepository;
    }

    /**
     * @return int|void
     */
    public function __invoke()
    {
        if (!$this->userState->isAssignedExercise()) {
            $this->output->printError("No active exercises. Select one from the menu");
            return 1;
        }

        $currentExercise = $this->userState->getCurrentExercise();
        $exercise = $this->exerciseRepository->findByName($currentExercise);

        $markDown = file_get_contents($exercise->getProblem());
        $this->output->write($this->markdownRenderer->render($markDown));
    }
}
