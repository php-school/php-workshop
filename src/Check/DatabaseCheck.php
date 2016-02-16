<?php

namespace PhpSchool\PhpWorkshop\Check;

use PDO;
use PhpSchool\PhpWorkshop\Event\CliEvent;
use PhpSchool\PhpWorkshop\Event\CliExecuteEvent;
use PhpSchool\PhpWorkshop\Event\Event;
use PhpSchool\PhpWorkshop\Event\EventDispatcher;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\Exercise\ExerciseType;
use PhpSchool\PhpWorkshop\Exercise\TemporaryDirectoryTrait;
use PhpSchool\PhpWorkshop\ExerciseCheck\DatabaseExerciseCheck;
use PhpSchool\PhpWorkshop\Result\Failure;
use PhpSchool\PhpWorkshop\Result\Success;
use Symfony\Component\Process\Process;

/**
 * Class DatabaseCheck
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class DatabaseCheck implements ListenableCheckInterface
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
     * @var
     */
    private $userDsn;

    /**
     * @var string
     */
    private $solutionDsn;

    /**
     *
     */
    public function __construct()
    {
        $this->databaseDirectory    = $this->getTemporaryPath();
        $this->userDatabasePath     = sprintf('%s/user-db.sqlite', $this->databaseDirectory);
        $this->solutionDatabasePath = sprintf('%s/solution-db.sqlite', $this->databaseDirectory);
        $this->solutionDsn          = sprintf('sqlite:%s', $this->solutionDatabasePath);
        $this->userDsn              = sprintf('sqlite:%s', $this->userDatabasePath);
    }

    /**
     * Return the check's name
     *
     * @return string
     */
    public function getName()
    {
        return 'Database Verification Check';
    }

    /**
     *
     * @return string
     */
    public function getExerciseInterface()
    {
        return DatabaseExerciseCheck::class;
    }

    /**
     * @param EventDispatcher $eventDispatcher
     */
    public function attach(EventDispatcher $eventDispatcher)
    {
        if (file_exists($this->databaseDirectory)) {
            throw new \RuntimeException(
                sprintf('Database directory: "%s" already exists', $this->databaseDirectory)
            );
        }

        mkdir($this->databaseDirectory, 0777, true);

        try {
            $db = new PDO($this->userDsn);
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (\PDOException $e) {
            rmdir($this->databaseDirectory);
            throw $e;
        }

        $eventDispatcher->listen('verify.start', function (Event $e) use ($db) {
            $e->getParameter('exercise')->seed($db);
            //make a copy - so solution can modify without effecting database user has access to
            copy($this->userDatabasePath, $this->solutionDatabasePath);
        });

        $eventDispatcher->listen('run.start', function (Event $e) use ($db) {
            $e->getParameter('exercise')->seed($db);
        });

        $eventDispatcher->listen('cli.verify.solution-execute.pre', function (CliExecuteEvent $e) {
            $e->prependArg($this->solutionDsn);
        });

        $eventDispatcher->listen(
            ['cli.verify.user-execute.pre', 'cli.run.user-execute.pre'],
            function (CliExecuteEvent $e) {
                $e->prependArg($this->userDsn);
            }
        );

        $eventDispatcher->insertVerifier('verify.finish', function (Event $e) use ($db) {
            $verifyResult = $e->getParameter('exercise')->verify($db);

            if (false === $verifyResult) {
                return Failure::fromNameAndReason($this->getName(), 'Database verification failed');
            }

            return new Success('Database Verification Check');
        });

        $eventDispatcher->listen(
            [
                'cli.verify.solution-execute.fail',
                'verify.finish',
                'run.finish'
            ],
            function (Event $e) use ($db) {
                unset($db);
                @unlink($this->userDatabasePath);
                @unlink($this->solutionDatabasePath);
                rmdir($this->databaseDirectory);
            }
        );
    }
}
