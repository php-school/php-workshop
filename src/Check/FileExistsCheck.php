<?php

namespace PhpSchool\PhpWorkshop\Check;

use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\Exercise\ExerciseType;
use PhpSchool\PhpWorkshop\Input\Input;
use PhpSchool\PhpWorkshop\Result\Failure;
use PhpSchool\PhpWorkshop\Result\ResultInterface;
use PhpSchool\PhpWorkshop\Result\Success;

/**
 * This check verifies that the student's solution file actually exists.
 */
class FileExistsCheck implements SimpleCheckInterface
{
    /**
     * Return the check's name.
     */
    public function getName(): string
    {
        return 'File Exists Check';
    }

    /**
     * Simply check that the file exists.
     *
     * @param ExerciseInterface $exercise The exercise to check against.
     * @param Input $input The command line arguments passed to the command.
     * @return ResultInterface The result of the check.
     */
    public function check(ExerciseInterface $exercise, Input $input): ResultInterface
    {
        if (file_exists($input->getRequiredArgument('program'))) {
            return Success::fromCheck($this);
        }

        return Failure::fromCheckAndReason(
            $this,
            sprintf('File: "%s" does not exist', $input->getRequiredArgument('program'))
        );
    }

    /**
     * This check can run on any exercise type.
     */
    public function canRun(ExerciseType $exerciseType): bool
    {
        return in_array($exerciseType->getValue(), [ExerciseType::CGI, ExerciseType::CLI], true);
    }

    public function getExerciseInterface(): string
    {
        return ExerciseInterface::class;
    }

    /**
     * This check must run before executing the solution becuase it may not exist.
     */
    public function getPosition(): string
    {
        return SimpleCheckInterface::CHECK_BEFORE;
    }
}
