<?php

namespace PhpSchool\PhpWorkshop\Command;

use PhpSchool\PhpWorkshop\ExerciseRepository;
use PhpSchool\PhpWorkshop\Output;
use PhpSchool\PhpWorkshop\UserState;
use Symfony\Component\Process\Process;

/**
 * Class RunCommand
 * @package PhpSchool\PhpWorkshop\Command
 * @author Michael Woodward <mikeymile.mw@gmail.com>
 */
class RunCommand
{
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
     * @param UserState $userState
     * @param Output $output
     */
    public function __construct(
        ExerciseRepository $exerciseRepository,
        UserState $userState,
        Output $output
    ) {
        $this->output               = $output;
        $this->exerciseRepository   = $exerciseRepository;
        $this->userState            = $userState;
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

        $exercise = $this->exerciseRepository->findByName($this->userState->getCurrentExercise());
        $args     = $exercise->getArgs();
        $cmd      = sprintf('%s %s %s', PHP_BINARY, $program, implode(' ', $args));
        $process  = new Process($cmd, dirname($program));

        $process->run();
        $this->output->write($process->getOutput());
    }
}
