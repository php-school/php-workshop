<?php

namespace PhpSchool\PhpWorkshop\ExerciseRunner;

use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\Output\OutputInterface;
use PhpSchool\PhpWorkshop\Result\ResultInterface;

/**
 * This interface describes how an exercise runner should work. Each exercise type
 * maps to an exercise runner.
 *
 * @package PhpSchool\PhpWorkshop\ExerciseRunner
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
interface ExerciseRunnerInterface
{
    /**
     * Get the name of the exercise runner.
     *
     * @return string
     */
    public function getName();

    /**
     * Verify a solution to an exercise. Verification involves executing the reference solution
     * and the student's solution and comparing their output. If the output is the same
     * an instance of `PhpSchool\PhpWorkshop\Result\SuccessInterface` should be returned, if the output
     * is not the same, or something else went wrong then an instance of
     * `\PhpSchool\PhpWorkshop\Result\FailureInterface` should be returned.
     *
     * Other things that could go wrong include the student's solution returning a non-zero
     * exit code, or a notice/warning being exhibited.
     *
     * @param string $fileName The absolute path to the student's solution.
     * @return ResultInterface The result of the check.
     */
    public function verify($fileName);

    /**
     * Run a solution to an exercise. This simply run's the student's solution with the correct input from the exercise
     * (such as the CLI arguments) and prints the output directly. This allows the student to have the environment
     * setup for them including getting a different set of arguments each time (if the exercise supports that).
     *
     * @param string $fileName The absolute path to the student's solution.
     * @param OutputInterface $output A wrapper around STDOUT.
     * @return bool If the solution was successfully executed, eg. exit code was 0.
     */
    public function run($fileName, OutputInterface $output);
}
