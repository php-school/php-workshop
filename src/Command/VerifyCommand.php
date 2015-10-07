<?php

namespace PhpWorkshop\PhpWorkshop\Command;

use Colors\Color;
use MikeyMike\CliMenu\Terminal\UnixTerminal;
use PhpWorkshop\PhpWorkshop\ExerciseRepository;
use PhpWorkshop\PhpWorkshop\ExerciseRunner;
use PhpWorkshop\PhpWorkshop\Output;
use PhpWorkshop\PhpWorkshop\Result\StdOutFailure;
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
        //echo "               "  . $color(str_repeat("─", $lineLength))->yellow() . "\n";
        echo $color(str_repeat("─", $width))->yellow() . "\n";

        $stdOutFailure = new StdOutFailure('lol', 'CHIPS AND GRAVYYY', 'COFFEE AND CIGARETTES');
        $partSize = $width / 2;
        $line = sprintf('  "%s"', $stdOutFailure->getActualOutput());

        $remaining = $partSize - strlen($line);

        //echo $color($line)->red() . "\n";

        echo "  " . $color("ACTUAL\n")->yellow()->bold()->underline();

        $actualOutput = $stdOutFailure->getActualOutput();

        $indent = function ($data) {
            return implode("\n", array_map(function ($line) {
                return "  " . $line;
            }, explode("\n", $data)));
        };

        echo $indent($color(sprintf('"%s"', $actualOutput))->red());

        echo "\n\n";
        echo "  " . $color("EXPECTED\n")->yellow()->bold()->underline();
        echo $indent($color(sprintf('"%s"', $stdOutFailure->getExpectedOutput()))->red());
        echo "\n\n";


        $parts = [
            " _ __ _ ",
            "/ |..| \\",
            '\\/ || \\/',
            " |_cool''_| "
        ];

//        foreach ($parts as $elephant) {
//            $half = strlen($elephant) / 2;
//            $pad = $middle - $half;
//            echo str_repeat(" ", $pad);
//            echo $color($elephant)->green() . "\n";
//        }
    }
}
