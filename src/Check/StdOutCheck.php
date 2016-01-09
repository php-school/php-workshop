<?php

namespace PhpSchool\PhpWorkshop\Check;

use PhpSchool\PhpWorkshop\Exception\CodeExecutionException;
use PhpSchool\PhpWorkshop\Exception\SolutionExecutionException;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\ExerciseCheck\StdOutExerciseCheck;
use PhpSchool\PhpWorkshop\ProcessExecutor\CliProcessExecutor;
use PhpSchool\PhpWorkshop\ProcessExecutor\CommandLineProcessExecutor;
use PhpSchool\PhpWorkshop\Result\Failure;
use PhpSchool\PhpWorkshop\Result\ResultInterface;
use PhpSchool\PhpWorkshop\Result\StdOutFailure;
use PhpSchool\PhpWorkshop\Result\Success;

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
     * @param string $fileName
     * @param array $args
     * @return string
     */
    private function executePhpFile($fileName, array $args)
    {
        $executor = new CliProcessExecutor($args);

        return $executor->executePhpFile($fileName);
    }
}
