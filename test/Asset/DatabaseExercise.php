<?php

namespace PhpSchool\PhpWorkshopTest\Asset;

use PDO;
use PhpSchool\PhpWorkshop\Check\DatabaseCheck;
use PhpSchool\PhpWorkshop\Environment\CliTestEnvironment;
use PhpSchool\PhpWorkshop\Event\EventDispatcher;
use PhpSchool\PhpWorkshop\Exercise\CliExercise;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\Exercise\ExerciseType;
use PhpSchool\PhpWorkshop\ExerciseCheck\DatabaseExerciseCheck;
use PhpSchool\PhpWorkshop\Solution\SolutionInterface;

class DatabaseExercise implements ExerciseInterface, DatabaseExerciseCheck, CliExercise
{
    private SolutionInterface $solution;
    private CliTestEnvironment $environment;
    private ?\Closure $verifier = null;
    private ?\Closure $seeder = null;

    public function setTestEnvironment(CliTestEnvironment $environment): void
    {
        $this->environment = $environment;
    }

    public function defineTestEnvironment(): CliTestEnvironment
    {
        return $this->environment;
    }

    public function setSeeder(\Closure $seeder): void
    {
        $this->seeder = $seeder;
    }

    public function seed(PDO $db): void
    {
        $seeder = $this->seeder;
        if ($seeder) {
            $seeder($db);
        }
    }

    public function setVerifier(\Closure $verifier): void
    {
        $this->verifier = $verifier;
    }

    public function verify(PDO $db): bool
    {
        $verifier = $this->verifier;

        if ($verifier) {
            return $verifier($db);
        }

        return true;
    }

    public function getName(): string
    {
        // TODO: Implement getName() method.
    }

    public function getType(): ExerciseType
    {
        return ExerciseType::CLI();
    }

    public function getProblem(): string
    {
        // TODO: Implement getProblem() method.
    }

    public function defineListeners(EventDispatcher $dispatcher): void
    {
        // TODO: Implement defineListeners() method.
    }

    public function getRequiredChecks(): array
    {
        return [DatabaseCheck::class];
    }

    public function getDescription(): string
    {
        // TODO: Implement getDescription() method.
    }

    public function tearDown(): void
    {
        // TODO: Implement tearDown() method.
    }

    public function setSolution(SolutionInterface $solution): void
    {
        $this->solution = $solution;
    }

    public function getSolution(): SolutionInterface
    {
        return $this->solution;
    }
}
