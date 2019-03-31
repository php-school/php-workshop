<?php

namespace PhpSchool\PhpWorkshop\Check;

use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\Exercise\ExerciseType;
use PhpSchool\PhpWorkshop\Input\Input;
use PhpSchool\PhpWorkshop\Result\ResultInterface;
use PhpSchool\PhpWorkshop\Result\Success;
use PhpSchool\PhpWorkshop\Result\Failure;
use Symfony\Component\Process\Process;

/**
 * This check attempts to lint a student's solution and returns
 * a success or failure based on the result of the linting.
 */
class PhpLintCheck implements SimpleCheckInterface
{

    /**
     * Return the check's name
     */
    public function getName() : string
    {
        return 'PHP Code Check';
    }

    /**
     * Simply check the student's solution can be linted with `php -l`.
     *
     * @param ExerciseInterface $exercise The exercise to check against.
     * @param Input $input The command line arguments passed to the command.
     * @return ResultInterface The result of the check.
     */
    public function check(ExerciseInterface $exercise, Input $input) : ResultInterface
    {
        $process = new Process(sprintf('%s -l %s', PHP_BINARY, $input->getArgument('program')));
        $process->run();

        if ($process->isSuccessful()) {
            return Success::fromCheck($this);
        }

        return Failure::fromCheckAndReason($this, trim($process->getOutput()));
    }

    /**
     * This check can run on any exercise type.
     */
    public function canRun(ExerciseType $exerciseType) : bool
    {
        return in_array($exerciseType->getValue(), [ExerciseType::CGI, ExerciseType::CLI], true);
    }

    public function getExerciseInterface() : string
    {
        return ExerciseInterface::class;
    }

    /**
     * This check should be run before executing the student's solution, as, if it cannot be linted
     * it probably cannot be executed.
     */
    public function getPosition() : string
    {
        return SimpleCheckInterface::CHECK_BEFORE;
    }
}
