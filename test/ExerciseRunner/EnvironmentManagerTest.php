<?php

namespace PhpSchool\PhpWorkshopTest\ExerciseRunner;

use PhpSchool\PhpWorkshop\Event\EventDispatcher;
use PhpSchool\PhpWorkshop\Event\ExerciseRunnerEvent;
use PhpSchool\PhpWorkshop\Exercise\MockExercise;
use PhpSchool\PhpWorkshop\Exercise\Scenario\CliScenario;
use PhpSchool\PhpWorkshop\ExerciseRunner\Context\TestContext;
use PhpSchool\PhpWorkshop\ExerciseRunner\EnvironmentManager;
use PhpSchool\PhpWorkshop\Input\Input;
use PhpSchool\PhpWorkshop\ResultAggregator;
use PhpSchool\PhpWorkshop\Solution\DirectorySolution;
use PhpSchool\PhpWorkshop\Solution\SingleFileSolution;
use PhpSchool\PhpWorkshopTest\Asset\CliExerciseImpl;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

class EnvironmentManagerTest extends TestCase
{
    public function testPrepareStudentCopiesAllScenarioFilesToExecutionDirectory(): void
    {
        $context = new TestContext();
        $context->createStudentSolutionDirectory();

        $scenario = (new CliScenario())
            ->withFile('file.txt', 'content')
            ->withFile('file2.txt', 'content2');

        $manager = new EnvironmentManager(new Filesystem(), new EventDispatcher(new ResultAggregator()));
        $manager->prepareStudent($context, $scenario);

        static::assertStringEqualsFile($context->getStudentExecutionDirectory() . '/file.txt', 'content');
        static::assertStringEqualsFile($context->getStudentExecutionDirectory() . '/file2.txt', 'content2');
    }

    public function testPrepareReferenceCopiesAllScenarioFilesAndSolutionFilesToExecutionDirectory(): void
    {
        $exercise = new CliExerciseImpl();
        $solution = SingleFileSolution::fromFile(realpath(__DIR__ . '/../res/cli/solution.php'));
        $exercise->setSolution($solution);

        $context = new TestContext($exercise);

        $scenario = (new CliScenario())
            ->withFile('file.txt', 'content')
            ->withFile('file2.txt', 'content2');

        $manager = new EnvironmentManager(new Filesystem(), new EventDispatcher(new ResultAggregator()));
        $manager->prepareReference($context, $scenario);

        static::assertFileEquals($context->getReferenceExecutionDirectory() . '/solution.php', __DIR__ . '/../res/cli/solution.php');
        static::assertStringEqualsFile($context->getReferenceExecutionDirectory() . '/file.txt', 'content');
        static::assertStringEqualsFile($context->getReferenceExecutionDirectory() . '/file2.txt', 'content2');
    }

    /**
     * @dataProvider finishEvents
     */
    public function testFileAreCleanedUpOnlyWhenFinishEventIsDispatched(string $eventName): void
    {
        $exercise = new CliExerciseImpl();
        $solution = SingleFileSolution::fromFile(realpath(__DIR__ . '/../res/cli/solution.php'));
        $exercise->setSolution($solution);

        $context = new TestContext($exercise);
        $context->createStudentSolutionDirectory();

        $scenario = (new CliScenario())
            ->withFile('file.txt', 'content')
            ->withFile('file2.txt', 'content2');

        $eventDispatcher = new EventDispatcher(new ResultAggregator());
        $manager = new EnvironmentManager(new Filesystem(), $eventDispatcher);
        $manager->prepareStudent($context, $scenario);
        $manager->prepareReference($context, $scenario);

        static::assertFileExists($context->getStudentExecutionDirectory());
        static::assertFileExists($context->getReferenceExecutionDirectory());

        $eventDispatcher->dispatch(new ExerciseRunnerEvent($eventName, $exercise, new Input('app', ['program' => ''])));

        static::assertFileExists($context->getStudentExecutionDirectory());
        static::assertFileNotExists($context->getReferenceExecutionDirectory() . '/file.txt');
        static::assertFileNotExists($context->getReferenceExecutionDirectory() . '/file2.txt');
        static::assertFileNotExists($context->getReferenceExecutionDirectory());
    }

    /**
     * @return array<array{0: string}>
     */
    public function finishEvents(): array
    {
        return [
            ['run.finish'],
            ['verify.finish'],
        ];
    }
}
