<?php

namespace PhpSchool\PhpWorkshop\ExerciseRunner;

use PhpSchool\PhpWorkshop\Exception\CodeExecutionException;
use PhpSchool\PhpWorkshop\Exception\SolutionExecutionException;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\ExerciseCheck\StdOutExerciseCheck;
use PhpSchool\PhpWorkshop\Output\OutputInterface;
use PhpSchool\PhpWorkshop\Result\Failure;
use PhpSchool\PhpWorkshop\Result\ResultInterface;
use PhpSchool\PhpWorkshop\Result\StdOutFailure;
use PhpSchool\PhpWorkshop\Result\Success;
use Symfony\Component\Process\Process;

/**
 * Class CliRunner
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */

class CliRunner implements ExerciseRunnerInterface
{

    /**
     * @return string
     */
    public function getName()
    {
        return 'CLI Program Runner';
    }

    /**
     * @param string $fileName
     * @param array $args
     * @return string
     */
    private function executePhpFile($fileName, array $args)
    {
        $cmd        = sprintf('%s %s %s', PHP_BINARY, $fileName, implode(' ', array_map('escapeshellarg', $args)));
        $process    = new Process($cmd, dirname($fileName));
        $process->run();

        if (!$process->isSuccessful()) {
            throw CodeExecutionException::fromProcess($process);
        }
        
        return $process->getOutput();
    }

    /**
     * @param ExerciseInterface $exercise
     * @param string $fileName
     * @return ResultInterface
     */
    public function verify(ExerciseInterface $exercise, $fileName)
    {
        if (!$exercise instanceof StdOutExerciseCheck) {
            throw new \InvalidArgumentException;
        }
        $args = $exercise->getArgs();

        try {
            $solutionOutput = $this->executePhpFile($exercise->getSolution()->getEntryPoint(), $args);
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
     * @param ExerciseInterface $exercise
     * @param string $fileName
     * @param OutputInterface $output
     * @return bool
     */
    public function run(ExerciseInterface $exercise, $fileName, OutputInterface $output)
    {
        if (!$exercise instanceof StdOutExerciseCheck) {
            throw new \InvalidArgumentException;
        }
        $args = $exercise->getArgs();

        $cmd        = sprintf('%s %s %s', PHP_BINARY, $fileName, implode(' ', array_map('escapeshellarg', $args)));
        $process    = new Process($cmd, dirname($fileName));

        $process->run(function ($outputType, $outputBuffer) use ($output) {
            $output->write($outputBuffer);
        });

        return $process->isSuccessful();
    }
}
