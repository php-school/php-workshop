<?php

namespace PhpWorkshop\PhpWorkshop\Check;

use PhpWorkshop\PhpWorkshop\Exception\SolutionExecutionException;
use PhpWorkshop\PhpWorkshop\Exercise\ExerciseInterface;
use PhpWorkshop\PhpWorkshop\Result\Failure;
use PhpWorkshop\PhpWorkshop\Result\ResultInterface;
use PhpWorkshop\PhpWorkshop\Result\StdOutFailure;
use PhpWorkshop\PhpWorkshop\Result\Success;
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
            throw new RuntimeException($process->getErrorOutput());
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
