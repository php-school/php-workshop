<?php

namespace PhpSchool\PhpWorkshop\Check;

use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\Exercise\ExerciseType;
use PhpSchool\PhpWorkshop\Result\Success;
use PhpSchool\PhpWorkshop\Result\Failure;
use Symfony\Component\Process\Process;

/**
 * Class PhpLintCheck
 * @package PhpSchool\PhpWorkshop\Check
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class PhpLintCheck implements SimpleCheckInterface
{

    /**
     * Return the check's name
     *
     * @return string
     */
    public function getName()
    {
        return 'PHP Code Check';
    }

    /**
     * Simply check the student's solution can be linted with `php -l`
     *
     * @param ExerciseInterface $exercise The exercise to check against.
     * @param string $fileName The absolute path to the student's solution.
     * @return ResultInterface The result of the check.
     */
    public function check(ExerciseInterface $exercise, $fileName)
    {
        $process = new Process(sprintf('%s -l %s', PHP_BINARY, $fileName));
        $process->run();

        if ($process->isSuccessful()) {
            return Success::fromCheck($this);
        }

        return Failure::fromCheckAndReason($this, $process->getErrorOutput());
    }

    /**
     * This check can run on any exercise type.
     *
     * @param ExerciseType $exerciseType
     * @return bool
     */
    public function canRun(ExerciseType $exerciseType)
    {
        return true;
    }

    /**
     * @return string
     */
    public function getExerciseInterface()
    {
        return ExerciseInterface::class;
    }

    /**
     * This check should be run before executing the student's solution, as, if it cannot be linted
     * it probably cannot be executed.
     *
     * @return string
     */
    public function getPosition()
    {
        return SimpleCheckInterface::CHECK_BEFORE;
    }
}
