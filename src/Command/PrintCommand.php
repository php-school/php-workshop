<?php

namespace PhpSchool\PhpWorkshop\Command;

use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\ExerciseRepository;
use PhpSchool\PhpWorkshop\MarkdownRenderer;
use PhpSchool\PhpWorkshop\Output\OutputInterface;
use PhpSchool\PhpWorkshop\UserState;

/**
 * A command to print the exercise's readme
 */
class PrintCommand
{
    /**
     * @var string
     */
    private $appName;

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
     * @param string $appName
     * @param ExerciseRepository $exerciseRepository
     * @param UserState $userState
     * @param MarkdownRenderer $markdownRenderer
     * @param OutputInterface $output
     */
    public function __construct(
        $appName,
        ExerciseRepository $exerciseRepository,
        UserState $userState,
        MarkdownRenderer $markdownRenderer,
        OutputInterface $output
    ) {
        $this->appName              = $appName;
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
        $currentExercise = $this->userState->getCurrentExercise();
        $exercise = $this->exerciseRepository->findByName($currentExercise);

        $markDown = (string) file_get_contents($exercise->getProblem());
        $doc = $this->markdownRenderer->render($markDown);
        $doc = str_replace('{appname}', $this->appName, $doc);
        $this->output->write($doc);
    }
}
