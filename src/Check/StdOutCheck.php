<?php

namespace PhpSchool\PhpWorkshop\Check;

use PhpSchool\PhpWorkshop\Exception\CodeExecutionException;
use PhpSchool\PhpWorkshop\Exception\SolutionExecutionException;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\ExerciseCheck\StdOutExerciseCheck;
use PhpSchool\PhpWorkshop\Result\Failure;
use PhpSchool\PhpWorkshop\Result\ResultInterface;
use PhpSchool\PhpWorkshop\Result\StdOutFailure;
use PhpSchool\PhpWorkshop\Result\Success;
use Symfony\Component\Process\Process;

/**
 * Class StdOutCheck
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */

class StdOutCheck implements CheckInterface
{

    /**
     * @return string
     */
    public function getName()
    {
        return 'Command Line Program Output Check';
    }

    /**
     * @param ExerciseInterface $exercise
     * @param string $fileName
     * @return ResultInterface
     */
    public function check(ExerciseInterface $exercise, $fileName)
    {
        if (!$exercise instanceof StdOutExerciseCheck) {
            throw new \InvalidArgumentException;
        }
        $args = $exercise->getArgs();

        try {
            $solutionOutput = $this->executePhpFile($exercise->getSolution(), $args);
        } catch (CodeExecutionException $e) {
            throw new SolutionExecutionException($e->getMessage());
        }

        try {
            $userOutput = $this->executePhpFile($fileName, $args);
        } catch (CodeExecutionException $e) {
            return Failure::fromCheckAndCodeExecutionFailure($this, $e);
        }
        if ($solutionOutput === $userOutput) {
            return Success::fromCheck($this);
        }

        return StdOutFailure::fromCheckAndOutput($this, $solutionOutput, $userOutput);
    }

    /**
     * @param $fileName
     * @param array $args
     * @return string
     */
    private function executePhpFile($fileName, array $args)
    {
        $cmd        = sprintf('%s %s %s', PHP_BINARY, $fileName, implode(' ', $args));
        $process    = new Process($cmd, dirname($fileName));
        $process->run();

        if (!$process->isSuccessful()) {
            throw CodeExecutionException::fromProcess($process);
        }

        return $process->getOutput();
    }
}
