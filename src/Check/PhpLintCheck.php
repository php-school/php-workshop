<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Check;

use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\Exercise\ExerciseType;
use PhpSchool\PhpWorkshop\ExerciseRunner\Context\ExecutionContext;
use PhpSchool\PhpWorkshop\Input\Input;
use PhpSchool\PhpWorkshop\Result\ResultInterface;
use PhpSchool\PhpWorkshop\Result\Success;
use PhpSchool\PhpWorkshop\Result\Failure;
use Symfony\Component\Process\ExecutableFinder;
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
    public function getName(): string
    {
        return 'PHP Code Check';
    }

    /**
     * Simply check the student's solution can be linted with `php -l`.
     *
     * @param ExecutionContext $context The current execution context, containing the exercise, input and working directories.
     * @return ResultInterface The result of the check.
     */
    public function check(ExecutionContext $context): ResultInterface
    {
        $finder = new ExecutableFinder();
        $process = new Process([$finder->find('php'), '-l', $context->getEntryPoint()]);
        $process->run();

        if ($process->isSuccessful()) {
            return Success::fromCheck($this);
        }

        return Failure::fromCheckAndReason($this, trim($process->getErrorOutput()) ?: trim($process->getOutput()));
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
     * This check should be run before executing the student's solution, as, if it cannot be linted
     * it probably cannot be executed.
     */
    public function getPosition(): string
    {
        return SimpleCheckInterface::CHECK_BEFORE;
    }
}
