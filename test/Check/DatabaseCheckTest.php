<?php

namespace PhpSchool\PhpWorkshopTest\Check;

use DI\ContainerBuilder;
use PDO;
use PhpSchool\PhpWorkshop\Check\CheckRepository;
use PhpSchool\PhpWorkshop\Check\DatabaseCheck;
use PhpSchool\PhpWorkshop\Event\EventDispatcher;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\Exercise\Scenario\CliScenario;
use PhpSchool\PhpWorkshop\ExerciseCheck\DatabaseExerciseCheck;
use PhpSchool\PhpWorkshop\ExerciseDispatcher;
use PhpSchool\PhpWorkshop\ExerciseRunner\CliRunner;
use PhpSchool\PhpWorkshop\ExerciseRunner\EnvironmentManager;
use PhpSchool\PhpWorkshop\ExerciseRunner\RunnerManager;
use PhpSchool\PhpWorkshop\Input\Input;
use PhpSchool\PhpWorkshop\Output\OutputInterface;
use PhpSchool\PhpWorkshop\Process\HostProcessFactory;
use PhpSchool\PhpWorkshop\ResultAggregator;
use PhpSchool\PhpWorkshop\Solution\SingleFileSolution;
use PhpSchool\PhpWorkshopTest\Asset\DatabaseExercise;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

class DatabaseCheckTest extends TestCase
{
    private DatabaseCheck $check;
    private CheckRepository $checkRepository;
    private ExerciseInterface $exercise;
    private string $dbDir;

    public function setUp(): void
    {
        $containerBuilder = new ContainerBuilder();
        $containerBuilder->addDefinitions(__DIR__ . '/../../app/config.php');
        $container = $containerBuilder->build();

        $this->checkRepository = $container->get(CheckRepository::class);

        $this->check = new DatabaseCheck();
        $this->exercise = new DatabaseExercise();
        $this->dbDir = sprintf(
            '%s/php-school/PhpSchool_PhpWorkshop_Check_DatabaseCheck',
            str_replace('\\', '/', realpath(sys_get_temp_dir())),
        );
    }

    public function testCheckMeta(): void
    {
        $this->assertEquals('Database Verification Check', $this->check->getName());
        $this->assertEquals(DatabaseExerciseCheck::class, $this->check->getExerciseInterface());
    }

    private function getRunnerManager(ExerciseInterface $exercise, EventDispatcher $eventDispatcher): MockObject
    {
        $runner = $this->getMockBuilder(CliRunner::class)
            ->setConstructorArgs([$exercise, $eventDispatcher, new HostProcessFactory(), new EnvironmentManager(new Filesystem(), $eventDispatcher)])
            ->onlyMethods(['getRequiredChecks'])
            ->getMock();

        $runner
            ->method('getRequiredChecks')
            ->willReturn([]);

        $runnerManager = $this->createMock(RunnerManager::class);
        $runnerManager
            ->expects($this->once())
            ->method('getRunner')
            ->willReturn($runner);

        return $runnerManager;
    }

    public function testSuccessIsReturnedIfDatabaseVerificationPassed(): void
    {
        $solution = SingleFileSolution::fromFile(realpath(__DIR__ . '/../res/database/solution.php'));
        $this->exercise->setSolution($solution);
        $this->exercise->setScenario((new CliScenario())->withExecution([1, 2, 3]));

        $this->exercise->setVerifier(fn() => true);
        $this->checkRepository->registerCheck($this->check);

        $results            = new ResultAggregator();
        $eventDispatcher    = new EventDispatcher($results);
        $dispatcher         = new ExerciseDispatcher(
            $this->getRunnerManager($this->exercise, $eventDispatcher),
            $results,
            $eventDispatcher,
            $this->checkRepository,
        );

        $dispatcher->verify($this->exercise, new Input('app', ['program' => __DIR__ . '/../res/database/user.php']));

        $this->assertTrue($results->isSuccessful());
    }

    public function testRunExercise(): void
    {
        $this->checkRepository->registerCheck($this->check);

        $results            = new ResultAggregator();
        $eventDispatcher    = new EventDispatcher($results);
        $dispatcher         = new ExerciseDispatcher(
            $this->getRunnerManager($this->exercise, $eventDispatcher),
            $results,
            $eventDispatcher,
            $this->checkRepository,
        );

        $dispatcher->run(
            $this->exercise,
            new Input('app', ['program' => __DIR__ . '/../res/database/user-solution-alter-db.php']),
            $this->createMock(OutputInterface::class),
        );
    }

    public function testFailureIsReturnedIfDatabaseVerificationFails(): void
    {
        $solution = SingleFileSolution::fromFile(realpath(__DIR__ . '/../res/database/solution.php'));
        $this->exercise->setSolution($solution);
        $this->exercise->setScenario((new CliScenario())->withExecution([1, 2, 3]));
        $this->exercise->setVerifier(fn() => false);

        $this->checkRepository->registerCheck($this->check);

        $results            = new ResultAggregator();
        $eventDispatcher    = new EventDispatcher($results);
        $dispatcher         = new ExerciseDispatcher(
            $this->getRunnerManager($this->exercise, $eventDispatcher),
            $results,
            $eventDispatcher,
            $this->checkRepository,
        );

        $dispatcher->verify($this->exercise, new Input('app', ['program' => __DIR__ . '/../res/database/user.php']));

        $this->assertFalse($results->isSuccessful());
        $results = iterator_to_array($results);
        $this->assertSame('Database verification failed', $results[1]->getReason());
    }

    public function testAlteringDatabaseInSolutionDoesNotEffectDatabaseInUserSolution(): void
    {
        $solution = SingleFileSolution::fromFile(realpath(__DIR__ . '/../res/database/solution-alter-db.php'));
        $this->exercise->setSolution($solution);
        $this->exercise->setScenario((new CliScenario())->withExecution());

        $this->exercise->setVerifier(function (PDO $db) {
            $users = $db->query('SELECT * FROM users');
            $users = $users->fetchAll(PDO::FETCH_ASSOC);

            $this->assertCount(2, $users);
            $this->assertEquals(
                [
                    ['id' => 1, 'name' => 'Jimi Hendrix', 'age' => '27', 'gender' => 'Male'],
                    ['id' => 2, 'name' => 'Kurt Cobain', 'age' => '27', 'gender' => 'Male'],
                ],
                $users,
            );

            return true;
        });

        $this->exercise->setSeeder(function (PDO $db) {
            $db->exec(
                'CREATE TABLE IF NOT EXISTS users (id INTEGER PRIMARY KEY, name TEXT, age INTEGER, gender TEXT)',
            );
            $stmt = $db->prepare('INSERT into users (name, age, gender) VALUES (:name, :age, :gender)');
            $stmt->execute([':name' => 'Jimi Hendrix', ':age' => 27, ':gender' => 'Male']);
        });

        $this->checkRepository->registerCheck($this->check);

        $results            = new ResultAggregator();
        $eventDispatcher    = new EventDispatcher($results);
        $dispatcher         = new ExerciseDispatcher(
            $this->getRunnerManager($this->exercise, $eventDispatcher),
            $results,
            $eventDispatcher,
            $this->checkRepository,
        );

        $dispatcher->verify(
            $this->exercise,
            new Input('app', ['program' => __DIR__ . '/../res/database/user-solution-alter-db.php']),
        );
    }

    protected function tearDown(): void
    {
        $fs = new Filesystem();
        $fs->remove($this->dbDir);
    }
}
