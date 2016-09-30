<?php

namespace PhpSchool\PhpWorkshop\ExerciseRunner;

use PhpSchool\PhpWorkshop\Exercise\CustomExercise;
use PhpSchool\PhpWorkshop\ExerciseDispatcher;
use PhpSchool\PhpWorkshop\Output\OutputInterface;
use PhpSchool\PhpWorkshop\Result\ResultInterface;

/**
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class CustomRunner implements ExerciseRunnerInterface
{
    /**
     * @var CustomExercise
     */
    private $exercise;

    /**
     * @param CustomExercise $exercise
     */
    public function __construct(CustomExercise $exercise)
    {
        $this->exercise = $exercise;
    }

    /**
     * Get the name of the exercise runner.
     *
     * @return string
     */
    public function getName()
    {
        return 'External Runner';
    }

    /**
     * Configure the exercise dispatcher. For example set the required checks
     * for this exercise type.
     *
     * @param ExerciseDispatcher $exerciseDispatcher
     * @return self
     */
    public function configure(ExerciseDispatcher $exerciseDispatcher)
    {
        return $this;
    }

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
    public function verify($fileName = null)
    {
        return $this->exercise->verify();
    }

    /**
     * Run a solution to an exercise. This simply run's the student's solution with the correct input from the exercise
     * (such as the CLI arguments) and prints the output directly. This allows the student to have the environment
     * setup for them including getting a different set of arguments each time (if the exercise supports that).
     *
     * @param string $fileName The absolute path to the student's solution.
     * @param OutputInterface $output A wrapper around STDOUT.
     * @return bool If the solution was successfully executed, eg. exit code was 0.
     */
    public function run($fileName = null, OutputInterface $output)
    {
        $message  = "Nothing to run here. This exercise does not require a code solution, ";
        $message .= "so there is nothing to execute.";
        $output->writeLine($message);
    }
}
