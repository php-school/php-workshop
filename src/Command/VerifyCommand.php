<?php

namespace PhpWorkshop\PhpWorkshop\Command;

use Colors\Color;
use MikeyMike\CliMenu\Terminal\UnixTerminal;
use PhpWorkshop\PhpWorkshop\ExerciseRepository;
use PhpWorkshop\PhpWorkshop\ExerciseRunner;
use PhpWorkshop\PhpWorkshop\Output;
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

        $exercise = $this->exerciseRepository->findByName($this->userState->getCurrentExercise());

        $result = $this->runner->runExercise($exercise, $program);
        var_dump($result->isSuccessful());
        var_dump($result->getErrors());




        $color = new Color;
        $terminal = new UnixTerminal;
        $width = $terminal->getWidth();
        $middle = $width / 2;


        $lineLength = ($width - 30);
        echo "               "  . $color(str_repeat("─", $lineLength))->yellow() . "\n";




        $parts = [
            " _ __ _ ",
            "/ |..| \\",
            '\\/ || \\/',
            " |_''_| "
        ];

        foreach ($parts as $elephant) {
            $half = strlen($elephant) / 2;
            $pad = $middle - $half;
            echo str_repeat(" ", $pad);
            echo $color($elephant)->green() . "\n";
        }
    }
}
