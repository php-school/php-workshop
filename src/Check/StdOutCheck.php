<?php

namespace PhpSchool\PhpWorkshop\Check;

use PhpSchool\PhpWorkshop\Exception\SolutionExecutionException;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\Result\Failure;
use PhpSchool\PhpWorkshop\Result\ResultInterface;
use PhpSchool\PhpWorkshop\Result\StdOutFailure;
use PhpSchool\PhpWorkshop\Result\Success;
use RuntimeException;
use Symfony\Component\Process\Process;

/**
 * Class StdOutCheck
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */

class StdOutCheck implements CheckInterface
{

    /**
     * @param ExerciseInterface $exercise
     * @param string $fileName
     * @return ResultInterface
     */
    public function check(ExerciseInterface $exercise, $fileName)
    {
        $args = $exercise->getArgs();

        try {
            $solutionOutput = $this->executePhpFile($exercise->getSolution(), $args);
        } catch (RuntimeException $e) {
            throw new SolutionExecutionException($e->getMessage());
        }

        try {
            $userOutput = $this->executePhpFile($fileName, $args);
        } catch (RuntimeException $e) {
            return new Failure('Program Output', sprintf('PHP Code failed to execute. Error: "%s"', $e->getMessage()));
        }


        if ($solutionOutput === $userOutput) {
            return new Success('Program Output');
        }

        return new StdOutFailure($solutionOutput, $userOutput);
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
            throw new RuntimeException($process->getErrorOutput() ? $process->getErrorOutput() : $process->getOutput());
        }

        return $process->getOutput();
    }

    /**
     * @return bool
     */
    public function breakChainOnFailure()
    {
        return false;
    }
}
