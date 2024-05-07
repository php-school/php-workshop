<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Check;

use PDO;
use PDOException;
use PhpSchool\PhpWorkshop\Event\CliExecuteEvent;
use PhpSchool\PhpWorkshop\Event\Event;
use PhpSchool\PhpWorkshop\Event\EventDispatcher;
use PhpSchool\PhpWorkshop\Exercise\TemporaryDirectoryTrait;
use PhpSchool\PhpWorkshop\ExerciseCheck\DatabaseExerciseCheck;
use PhpSchool\PhpWorkshop\Result\Failure;
use PhpSchool\PhpWorkshop\Result\Success;
use RuntimeException;

/**
 * This check sets up a database and a `PDO` object. It prepends the database DSN as a CLI argument to the student's
 * solution so they can connect to the database. The `PDO` object is passed to the exercise before and after the
 * student's solution has been executed, allowing you to first seed the database and then verify the contents of the
 * database.
 */
class DatabaseCheck implements ListenableCheckInterface
{
    use TemporaryDirectoryTrait;

    private string $databaseDirectory;
    private string $userDatabasePath;
    private string $solutionDatabasePath;
    private string $userDsn;
    private string $solutionDsn;

    /**
     * Setup paths and DSN's.
     */
    public function __construct()
    {
        $this->databaseDirectory = $this->getTemporaryPath();
        $this->userDatabasePath = sprintf('%s/user-db.sqlite', $this->databaseDirectory);
        $this->solutionDatabasePath = sprintf('%s/solution-db.sqlite', $this->databaseDirectory);
        $this->solutionDsn = sprintf('sqlite:%s', $this->solutionDatabasePath);
        $this->userDsn = sprintf('sqlite:%s', $this->userDatabasePath);
    }

    /**
     * Return the check's name
     */
    public function getName(): string
    {
        return 'Database Verification Check';
    }

    public function getExerciseInterface(): string
    {
        return DatabaseExerciseCheck::class;
    }

    /**
     * Here we attach to various events to seed, verify and inject the DSN's
     * to the student & reference solution programs' CLI arguments.
     *
     * @param EventDispatcher $eventDispatcher
     */
    public function attach(EventDispatcher $eventDispatcher): void
    {
        if (file_exists($this->databaseDirectory)) {
            throw new RuntimeException(
                sprintf('Database directory: "%s" already exists', $this->databaseDirectory)
            );
        }

        mkdir($this->databaseDirectory, 0777, true);

        try {
            $db = new PDO($this->userDsn);
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            rmdir($this->databaseDirectory);
            throw $e;
        }

        $eventDispatcher->listen('verify.start', function (Event $e) use ($db) {
            /** @var DatabaseExerciseCheck $exercise */
            $exercise = $e->getParameter('exercise');
            $exercise->seed($db);
            //make a copy - so solution can modify without effecting database user has access to
            copy($this->userDatabasePath, $this->solutionDatabasePath);
        });

        $eventDispatcher->listen('run.start', function (Event $e) use ($db) {
            /** @var DatabaseExerciseCheck $exercise */
            $exercise = $e->getParameter('exercise');
            $exercise->seed($db);
        });

        $eventDispatcher->listen('cli.verify.reference-execute.pre', function (CliExecuteEvent $e) {
            $e->prependArg($this->solutionDsn);
        });

        $eventDispatcher->listen(
            ['cli.verify.student-execute.pre', 'cli.run.student-execute.pre'],
            function (CliExecuteEvent $e) {
                $e->prependArg($this->userDsn);
            }
        );

        $eventDispatcher->insertVerifier('verify.finish', function (Event $e) use ($db) {
            /** @var DatabaseExerciseCheck $exercise */
            $exercise = $e->getParameter('exercise');
            $verifyResult = $exercise->verify($db);

            if (false === $verifyResult) {
                return Failure::fromNameAndReason($this->getName(), 'Database verification failed');
            }

            return new Success('Database Verification Check');
        });

        $eventDispatcher->listen(
            [
                'cli.verify.reference-execute.fail',
                'verify.finish',
                'run.finish'
            ],
            function () use ($db) {
                unset($db);
                $this->unlink($this->userDatabasePath);
                $this->unlink($this->solutionDatabasePath);
                rmdir($this->databaseDirectory);
            }
        );
    }

    private function unlink(string $file): void
    {
        if (file_exists($file)) {
            unlink($file);
        }
    }
}
