<?php

namespace PhpSchool\PhpWorkshop\Check;

use PDO;
use PhpSchool\PhpWorkshop\Exception\CodeExecutionException;
use PhpSchool\PhpWorkshop\Exception\SolutionExecutionException;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\ExerciseCheck\DatabaseExerciseCheck;
use PhpSchool\PhpWorkshop\ExerciseCheck\StdOutExerciseCheck;
use PhpSchool\PhpWorkshop\Result\Failure;
use PhpSchool\PhpWorkshop\Result\ResultInterface;
use PhpSchool\PhpWorkshop\Result\StdOutFailure;
use PhpSchool\PhpWorkshop\Result\Success;
use Symfony\Component\Process\Process;

/**
 * Class DatabaseCheck
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */

class DatabaseCheck implements CheckInterface
{

    /**
     * @return string
     */
    public function getName()
    {
        return 'Database Check';
    }

    /**
     * @param ExerciseInterface $exercise
     * @param string $fileName
     * @return ResultInterface
     */
    public function check(ExerciseInterface $exercise, $fileName)
    {
        if (!$exercise instanceof DatabaseExerciseCheck) {
            throw new \InvalidArgumentException;
        }

        $solutionDbPath = sprintf('sqlite:%s/solution-db.sqlite', $this->getTemporaryPath());
        $userDbPath     = sprintf('sqlite:%s/user-db.sqlite', $this->getTemporaryPath());

        $db = new PDO($userDbPath);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $exercise->seed($db);
        
        //make a copy - so solution can modify without effecting database user has access to
        copy($userDbPath, $solutionDbPath);
        
        $args = $exercise->getArgs();
        
        try {
            $solutionOutput = $this->executePhpFile($exercise->getSolution(), array_merge([$solutionDbPath], $args));
        } catch (CodeExecutionException $e) {
            throw new SolutionExecutionException($e->getMessage());
        }

        try {
            $userOutput = $this->executePhpFile($fileName, array_merge([$userDbPath], $args));
        } catch (CodeExecutionException $e) {
            return Failure::fromCheckAndCodeExecutionFailure($this, $e);
        }
        
        $verifyResult = $exercise->verify($db);

        //cleanup db's
        unset($db);
        unlink($userDbPath);
        unlink($solutionDbPath);
        
        if (false === $verifyResult) {
            return Failure::fromCheckAndReason($this, 'Database verification failed');
        }
        
        if ($solutionOutput !== $userOutput) {
            return StdOutFailure::fromCheckAndOutput($this, $solutionOutput, $userOutput);
        }

        return Success::fromCheck($this);
    }

    /**
     * @param $fileName
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
     * @return string
     */
    private function getTemporaryPath()
    {
        return sprintf(
            '%s/%s',
            str_replace('\\', '/', realpath(sys_get_temp_dir())),
            str_replace('\\', '_', __CLASS__)
        );
    }
}
