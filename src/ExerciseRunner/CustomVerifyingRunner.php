<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\ExerciseRunner;

use PhpSchool\PhpWorkshop\Exercise\CustomVerifyingExercise;
use PhpSchool\PhpWorkshop\ExerciseRunner\Context\ExecutionContext;
use PhpSchool\PhpWorkshop\Input\Input;
use PhpSchool\PhpWorkshop\Output\OutputInterface;
use PhpSchool\PhpWorkshop\Result\ResultInterface;

/**
 * The `CUSTOM` runner. This runner delegates to the exercise for verifying.
 */
class CustomVerifyingRunner implements ExerciseRunnerInterface
{
    public function __construct(private CustomVerifyingExercise $exercise) {}

    /**
     * Get the name of the exercise runner.
     *
     * @return string
     */
    public function getName(): string
    {
        return 'Custom Verifying Runner';
    }

    /**
     * Get an array of the class names of the required checks this runner needs.
     *
     * @return array<class-string>
     */
    public function getRequiredChecks(): array
    {
        return [];
    }

    /**
     * Delegate to the exercise for verifying. Verifying could mean checking that a program was installed or that some
     * other arbitrary task was performed.
     *
     * @param ExecutionContext $context The current execution context, containing the exercise, input and working directories.
     * @return ResultInterface The result of the check.
     */
    public function verify(ExecutionContext $context): ResultInterface
    {
        return $this->exercise->verify();
    }

    /**
     * Running a custom verifying exercise does nothing. There is no program required, therefore there is nothing
     * to run.
     *
     * @param ExecutionContext $context The current execution context, containing the exercise, input and working directories.
     * @param OutputInterface $output A wrapper around STDOUT.
     * @return bool If the solution was successfully executed, eg. exit code was 0.
     */
    public function run(ExecutionContext $context, OutputInterface $output): bool
    {
        $message  = 'Nothing to run here. This exercise does not require a code solution, ';
        $message .= 'so there is nothing to execute.';
        $output->writeLine($message);
        return true;
    }
}
