<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Check;

use PDO;
use PhpSchool\PhpWorkshop\Event\CgiExerciseRunnerEvent;
use PhpSchool\PhpWorkshop\Event\CliExecuteEvent;
use PhpSchool\PhpWorkshop\Event\CliExerciseRunnerEvent;
use PhpSchool\PhpWorkshop\Event\Event;
use PhpSchool\PhpWorkshop\Event\EventDispatcher;
use PhpSchool\PhpWorkshop\Event\ExerciseRunnerEvent;
use PhpSchool\PhpWorkshop\ExerciseCheck\DatabaseExerciseCheck;
use PhpSchool\PhpWorkshop\Result\Failure;
use PhpSchool\PhpWorkshop\Result\Success;
use PhpSchool\PhpWorkshop\Utils\Path;
use PhpSchool\PhpWorkshop\Utils\System;
use Symfony\Component\Filesystem\Filesystem;

/**
 * This check sets up a database and a `PDO` object. It prepends the database DSN as a CLI argument to the student's
 * solution so they can connect to the database. The `PDO` object is passed to the exercise before and after the
 * student's solution has been executed, allowing you to first seed the database and then verify the contents of the
 * database.
 */
class DatabaseCheck implements ListenableCheckInterface
{
    private Filesystem $filesystem;
    private ?string $dbContent = null;

    public function __construct(Filesystem $filesystem = null)
    {
        $this->filesystem = $filesystem ? $filesystem : new Filesystem();
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
        $eventDispatcher->listen(['verify.start', 'run.start'], function (Event $e) {
            $path = System::randomTempPath('sqlite');

            $this->filesystem->touch($path);

            try {
                $db = $this->getPDO($path);

                /** @var DatabaseExerciseCheck $exercise */
                $exercise = $e->getParameter('exercise');
                $exercise->seed($db);

                $this->dbContent = (string) file_get_contents($path);
            } finally {
                unset($db);

                $this->filesystem->remove($path);
            }
        });

        $eventDispatcher->listen(
            ['cli.verify.prepare', 'cgi.verify.prepare'],
            function (CliExerciseRunnerEvent|CgiExerciseRunnerEvent $e) {
                $e->getScenario()->withFile('db.sqlite', (string) $this->dbContent);

                $this->dbContent = null;
            },
        );

        $eventDispatcher->listen(
            'cli.verify.reference-execute.pre',
            fn(CliExecuteEvent $e) => $e->prependArg('sqlite:db.sqlite'),
        );

        $eventDispatcher->listen(
            ['cli.verify.student-execute.pre', 'cli.run.student-execute.pre'],
            fn(CliExecuteEvent $e) => $e->prependArg('sqlite:db.sqlite'),
        );

        $eventDispatcher->insertVerifier('verify.finish', function (ExerciseRunnerEvent $e) {
            $db = $this->getPDO(Path::join($e->getContext()->getStudentExecutionDirectory(), 'db.sqlite'));

            try {
                /** @var DatabaseExerciseCheck $exercise */
                $exercise = $e->getParameter('exercise');
                $verifyResult = $exercise->verify($db);

                if (false === $verifyResult) {
                    return Failure::fromNameAndReason($this->getName(), 'Database verification failed');
                }

                return new Success('Database Verification Check');
            } finally {
                unset($db);
            }
        });
    }

    private function getPDO(string $path): PDO
    {
        $db = new PDO('sqlite:' . $path);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return $db;
    }
}
