<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Command;

use PhpSchool\PhpWorkshop\Exception\ProblemFileDoesNotExistException;
use PhpSchool\PhpWorkshop\ExerciseRepository;
use PhpSchool\PhpWorkshop\MarkdownRenderer;
use PhpSchool\PhpWorkshop\Output\OutputInterface;
use PhpSchool\PhpWorkshop\UserState\UserState;

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
        string $appName,
        ExerciseRepository $exerciseRepository,
        UserState $userState,
        MarkdownRenderer $markdownRenderer,
        OutputInterface $output,
    ) {
        $this->appName = $appName;
        $this->markdownRenderer = $markdownRenderer;
        $this->output = $output;
        $this->userState = $userState;
        $this->exerciseRepository = $exerciseRepository;
    }

    /**
     * @return void
     */
    public function __invoke(): void
    {
        $currentExercise = $this->userState->getCurrentExercise();
        $exercise = $this->exerciseRepository->findByName($currentExercise);

        $problemFile = $exercise->getProblem();

        if (!is_readable($problemFile)) {
            throw ProblemFileDoesNotExistException::fromFile($problemFile);
        }

        $markDown = (string) file_get_contents($problemFile);

        $doc = $this->markdownRenderer->render($markDown);
        $doc = str_replace('{appname}', $this->appName, $doc);
        $this->output->write($doc);
    }
}
