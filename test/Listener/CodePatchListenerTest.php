<?php

namespace PhpSchool\PhpWorkshopTest\Listener;

use PhpSchool\PhpWorkshop\CodePatcher;
use PhpSchool\PhpWorkshop\Event\ExerciseRunnerEvent;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\ExerciseRunner\Context\TestContext;
use PhpSchool\PhpWorkshop\Input\Input;
use PhpSchool\PhpWorkshop\Listener\CodePatchListener;
use PhpSchool\PhpWorkshop\Utils\Path;
use PhpSchool\PhpWorkshop\Utils\System;
use PhpSchool\PhpWorkshopTest\Asset\ProvidesSolutionExercise;
use PhpSchool\PhpWorkshopTest\BaseTest;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Filesystem\Filesystem;

class CodePatchListenerTest extends BaseTest
{
    /**
     * @var CodePatcher
     */
    private $codePatcher;

    public function setUp(): void
    {
        $this->codePatcher = $this->createMock(CodePatcher::class);
    }

    public function testPatchUpdatesCode(): void
    {
        $exercise = $this->createMock(ExerciseInterface::class);
        $context = TestContext::withEnvironment($exercise);

        $this->createFileInEnvironment(
            $context->studentExecutionDirectory,
            'solution.php',
            'ORIGINAL CONTENT'
        );

        $this->codePatcher
            ->expects($this->once())
            ->method('patch')
            ->with($exercise, 'ORIGINAL CONTENT')
            ->willReturn('MODIFIED CONTENT');

        $listener   = new CodePatchListener($this->codePatcher, new NullLogger(), false);
        $event      = new ExerciseRunnerEvent('event', $context);
        $listener->patch($event);

        self::assertStringEqualsFile(
            Path::join($context->studentExecutionDirectory, 'solution.php'),
            'MODIFIED CONTENT'
        );
    }

    public function testRevertAfterPatch(): void
    {
        $exercise = $this->createMock(ExerciseInterface::class);
        $context = TestContext::withEnvironment($exercise);

        $this->createFileInEnvironment(
            $context->studentExecutionDirectory,
            'solution.php',
            'ORIGINAL CONTENT'
        );

        $this->codePatcher
            ->expects($this->once())
            ->method('patch')
            ->with($exercise, 'ORIGINAL CONTENT')
            ->willReturn('MODIFIED CONTENT');

        $listener   = new CodePatchListener($this->codePatcher, new NullLogger(), false);
        $event      = new ExerciseRunnerEvent('event', $context);
        $listener->patch($event);
        $listener->revert($event);

        self::assertStringEqualsFile(
            Path::join($context->studentExecutionDirectory, 'solution.php'),
            'ORIGINAL CONTENT'
        );
    }

    public function testPatchesProvidedSolution(): void
    {
        $exercise = new ProvidesSolutionExercise();
        $context = TestContext::withEnvironment($exercise);

        $this->createFileInEnvironment(
            $context->studentExecutionDirectory,
            'solution.php',
            'ORIGINAL CONTENT'
        );

        foreach ($exercise->getSolution()->getFiles() as $file) {
            $this->createFileInEnvironment(
                $context->referenceExecutionDirectory,
                $file->getRelativePath(),
                file_get_contents($file->getAbsolutePath())
            );
        }

        $this->codePatcher
            ->expects($this->exactly(2))
            ->method('patch')
            ->withConsecutive([$exercise, 'ORIGINAL CONTENT'], [$exercise, "<?php\n\necho 'Hello World';\n"])
            ->willReturn('MODIFIED CONTENT');

        $listener   = new CodePatchListener($this->codePatcher, new NullLogger(), false);
        $event      = new ExerciseRunnerEvent('event', $context);
        $listener->patch($event);

        self::assertStringEqualsFile(
            Path::join($context->studentExecutionDirectory, 'solution.php'),
            'MODIFIED CONTENT'
        );
        self::assertStringEqualsFile(
            Path::join(
                $context->referenceExecutionDirectory,
                $exercise->getSolution()->getEntryPoint()->getRelativePath(),
            ),
            'MODIFIED CONTENT'
        );
    }

    public function testFileIsLoggedWhenPatches(): void
    {
        $exercise = $this->createMock(ExerciseInterface::class);
        $context = TestContext::withEnvironment($exercise);

        $this->createFileInEnvironment(
            $context->studentExecutionDirectory,
            'solution.php',
            'ORIGINAL CONTENT'
        );

        $this->codePatcher
            ->expects($this->once())
            ->method('patch')
            ->with($exercise, 'ORIGINAL CONTENT')
            ->willReturn('MODIFIED CONTENT');

        $path = Path::join($context->studentExecutionDirectory, 'solution.php');
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('debug')
            ->with('Patching file: ' . $path);

        $listener  = new CodePatchListener($this->codePatcher, $logger, false);
        $event      = new ExerciseRunnerEvent('event', $context);
        $listener->patch($event);
    }

    public function testRevertDoesNotRevertStudentSubmissionPatchIfInDebugMode(): void
    {
        $exercise = $this->createMock(ExerciseInterface::class);
        $context = TestContext::withEnvironment($exercise);

        $this->createFileInEnvironment(
            $context->studentExecutionDirectory,
            'solution.php',
            'ORIGINAL CONTENT'
        );

        $this->codePatcher
            ->expects($this->once())
            ->method('patch')
            ->with($exercise, 'ORIGINAL CONTENT')
            ->willReturn('MODIFIED CONTENT');

        $listener   = new CodePatchListener($this->codePatcher, new NullLogger(), true);
        $event      = new ExerciseRunnerEvent('event', $context);
        $listener->patch($event);
        $listener->revert($event);

        self::assertStringEqualsFile(
            Path::join($context->studentExecutionDirectory, 'solution.php'),
            'MODIFIED CONTENT'
        );
    }
}
