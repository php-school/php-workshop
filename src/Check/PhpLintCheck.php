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
     * @return string
     */
    public function getName()
    {
        return 'PHP Code Check';
    }

    /**
     * @param ExerciseInterface $exercise
     * @param string $fileName
     * @return Failure|Success
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
     * @param ExerciseType $exerciseType
     * @return bool
     */
    public function canRun(ExerciseType $exerciseType)
    {
        return true;
    }

    /**
     *
     * @return string
     */
    public function getExerciseInterface()
    {
        return ExerciseInterface::class;
    }
}
