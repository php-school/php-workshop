<?php

namespace PhpSchool\PhpWorkshop\Check;

use PDO;
use PhpSchool\PhpWorkshop\Exception\CodeExecutionException;
use PhpSchool\PhpWorkshop\Exception\SolutionExecutionException;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\Exercise\TemporaryDirectoryTrait;
use PhpSchool\PhpWorkshop\ExerciseCheck\DatabaseExerciseCheck;
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
    use TemporaryDirectoryTrait;

    /**
     * @var string
     */
    private $databaseDirectory;

    /**
     * @var string
     */
    private $userDatabasePath;

    /**
     * @var string
     */
    private $solutionDatabasePath;

    /**
     *
     */
    public function __construct()
    {
        $this->databaseDirectory    = $this->getTemporaryPath();
        $this->userDatabasePath     = sprintf('%s/user-db.sqlite', $this->databaseDirectory);
        $this->solutionDatabasePath = sprintf('%s/solution-db.sqlite', $this->databaseDirectory);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'Database Check';
    }

    /**
     * @param ExerciseInterface $exercise
     * @param string q$fileName
     * @return ResultInterface
     */
    public function check(ExerciseInterface $exercise, $fileName)
    {
        if (!$exercise instanceof DatabaseExerciseCheck) {
            throw new \InvalidArgumentException;
        }

        if (file_exists($this->databaseDirectory)) {
            throw new \RuntimeException(
                sprintf('Database directory: "%s" already exists', $this->databaseDirectory)
            );
        }
        mkdir($this->databaseDirectory, 0777, true);
        $solutionDsn    = sprintf('sqlite:%s', $this->solutionDatabasePath);
        $userDsn        = sprintf('sqlite:%s', $this->userDatabasePath);

        $db = new PDO($userDsn);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $exercise->seed($db);
        
        //make a copy - so solution can modify without effecting database user has access to
        copy($this->userDatabasePath, $this->solutionDatabasePath);
        
        $args = $exercise->getArgs();
        
        try {
            $solutionOutput = $this->executePhpFile(
                $exercise->getSolution()->getEntryPoint(),
                array_merge([$solutionDsn], $args)
            );
        } catch (CodeExecutionException $e) {
            $this->cleanup($db);
            throw new SolutionExecutionException($e->getMessage());
        }

        try {
            $userOutput = $this->executePhpFile($fileName, array_merge([$userDsn], $args));
        } catch (CodeExecutionException $e) {
            $this->cleanup($db);
            return Failure::fromCheckAndCodeExecutionFailure($this, $e);
        }
        
        $verifyResult = $exercise->verify($db);
        
        $this->cleanup($db);
        
        if (false === $verifyResult) {
            //TODO: Custom failure describing database verification failures
            return Failure::fromCheckAndReason($this, 'Database verification failed');
        }
        
        if ($solutionOutput !== $userOutput) {
            return StdOutFailure::fromCheckAndOutput($this, $solutionOutput, $userOutput);
        }

        return Success::fromCheck($this);
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
     * Clean up databases
     * @param PDO $db
     */
    private function cleanup(PDO $db)
    {
        unset($db);
        unlink($this->userDatabasePath);
        unlink($this->solutionDatabasePath);
        rmdir($this->databaseDirectory);
    }
}
