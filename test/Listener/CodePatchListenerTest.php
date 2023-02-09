<?php

namespace PhpSchool\PhpWorkshopTest\Listener;

use PhpSchool\PhpWorkshop\CodePatcher;
use PhpSchool\PhpWorkshop\Event\ExerciseRunnerEvent;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\Input\Input;
use PhpSchool\PhpWorkshop\Listener\CodePatchListener;
use PhpSchool\PhpWorkshop\Utils\System;
use PhpSchool\PhpWorkshopTest\Asset\ProvidesSolutionExercise;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Filesystem\Filesystem;

class CodePatchListenerTest extends TestCase
{
    /**
     * @var string
     */
    private $file;

    /**
     * @var string
     */
    private $solution;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var CodePatcher
     */
    private $codePatcher;

    public function setUp(): void
    {
        $this->filesystem = new Filesystem();
        $this->codePatcher = $this->createMock(CodePatcher::class);

        $this->file = sprintf('%s/%s/submission.php', System::tempDir(), $this->getName());
        mkdir(dirname($this->file), 0775, true);
        touch($this->file);

        $this->solution = sprintf('%s/%s/solution.php', System::tempDir(), $this->getName());
        touch($this->solution);
    }

    public function tearDown(): void
    {
        $this->filesystem->remove(dirname($this->file));
    }

    public function testPatchUpdatesCode(): void
    {
        file_put_contents($this->file, 'ORIGINAL CONTENT');

        $input    = new Input('app', ['program' => $this->file]);
        $exercise = $this->createMock(ExerciseInterface::class);

        $this->codePatcher
            ->expects($this->once())
            ->method('patch')
            ->with($exercise, 'ORIGINAL CONTENT')
            ->willReturn('MODIFIED CONTENT');

        $listener   = new CodePatchListener($this->codePatcher, new NullLogger(), false);
        $event      = new ExerciseRunnerEvent('event', $exercise, $input);
        $listener->patch($event);

        self::assertStringEqualsFile($this->file, 'MODIFIED CONTENT');
    }

    public function testRevertAfterPatch(): void
    {
        file_put_contents($this->file, 'ORIGINAL CONTENT');

        $input    = new Input('app', ['program' => $this->file]);
        $exercise = $this->createMock(ExerciseInterface::class);

        $this->codePatcher
            ->expects($this->once())
            ->method('patch')
            ->with($exercise, 'ORIGINAL CONTENT')
            ->willReturn('MODIFIED CONTENT');

        $listener   = new CodePatchListener($this->codePatcher, new NullLogger(), false);
        $event      = new ExerciseRunnerEvent('event', $exercise, $input);
        $listener->patch($event);
        $listener->revert($event);

        self::assertStringEqualsFile($this->file, 'ORIGINAL CONTENT');
    }

    public function testPatchesProvidedSolution(): void
    {
        file_put_contents($this->file, 'ORIGINAL CONTENT');

        $input    = new Input('app', ['program' => $this->file]);
        $exercise = new ProvidesSolutionExercise();

        $this->codePatcher
            ->expects($this->exactly(2))
            ->method('patch')
            ->withConsecutive([$exercise, 'ORIGINAL CONTENT'], [$exercise, "<?php\n\necho 'Hello World';\n"])
            ->willReturn('MODIFIED CONTENT');

        $listener   = new CodePatchListener($this->codePatcher, new NullLogger(), false);
        $event      = new ExerciseRunnerEvent('event', $exercise, $input);
        $listener->patch($event);

        self::assertStringEqualsFile($this->file, 'MODIFIED CONTENT');
        self::assertStringEqualsFile($exercise->getSolution()->getEntryPoint()->getAbsolutePath(), 'MODIFIED CONTENT');
    }

    public function testFileIsLoggedWhenPatches(): void
    {
        file_put_contents($this->file, 'ORIGINAL CONTENT');

        $input    = new Input('app', ['program' => $this->file]);
        $exercise = $this->createMock(ExerciseInterface::class);

        $this->codePatcher
            ->expects($this->once())
            ->method('patch')
            ->with($exercise, 'ORIGINAL CONTENT')
            ->willReturn('MODIFIED CONTENT');

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('debug')
            ->with('Patching file: ' . $this->file);

        $listener  = new CodePatchListener($this->codePatcher, $logger, false);
        $event      = new ExerciseRunnerEvent('event', $exercise, $input);
        $listener->patch($event);
    }

    public function testRevertDoesNotRevertStudentSubmissionPatchIfInDebugMode(): void
    {
        file_put_contents($this->file, 'ORIGINAL CONTENT');

        $input    = new Input('app', ['program' => $this->file]);
        $exercise = $this->createMock(ExerciseInterface::class);

        $this->codePatcher
            ->expects($this->once())
            ->method('patch')
            ->with($exercise, 'ORIGINAL CONTENT')
            ->willReturn('MODIFIED CONTENT');

        $listener   = new CodePatchListener($this->codePatcher, new NullLogger(), true);
        $event      = new ExerciseRunnerEvent('event', $exercise, $input);
        $listener->patch($event);
        $listener->revert($event);

        self::assertStringEqualsFile($this->file, 'MODIFIED CONTENT');
    }
}
