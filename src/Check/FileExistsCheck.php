<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Check;

use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\Exercise\ExerciseType;
use PhpSchool\PhpWorkshop\ExerciseRunner\Context\ExecutionContext;
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
     * @param ExecutionContext $context The current execution context, containing the exercise, input and working directories.
     * @return ResultInterface The result of the check.
     */
    public function check(ExecutionContext $context): ResultInterface
    {
        if (file_exists($context->getEntryPoint())) {
            return Success::fromCheck($this);
        }

        return Failure::fromCheckAndReason(
            $this,
            sprintf('File: "%s" does not exist', $context->getEntryPoint())
        );
    }

    /**
     * This check can run on any exercise type.
     *
     * @param ExerciseType $exerciseType
     * @return bool
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
     * This check must run before executing the solution because it may not exist.
     */
    public function getPosition(): string
    {
        return SimpleCheckInterface::CHECK_BEFORE;
    }
}
