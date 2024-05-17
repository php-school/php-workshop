<?php

namespace PhpSchool\PhpWorkshopTest\Listener;

use PhpSchool\PhpWorkshop\Event\ExerciseRunnerEvent;
use PhpSchool\PhpWorkshop\ExerciseRunner\Context\TestContext;
use PhpSchool\PhpWorkshop\Listener\PrepareSolutionListener;
use PhpSchool\PhpWorkshop\Process\HostProcessFactory;
use PhpSchool\PhpWorkshop\Process\ProcessNotFoundException;
use PhpSchool\PhpWorkshop\Solution\SolutionInterface;
use PhpSchool\PhpWorkshopTest\Asset\CliExerciseImpl;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\ExecutableFinder;
use Yoast\PHPUnitPolyfills\Polyfills\AssertionRenames;

class PrepareSolutionListenerTest extends TestCase
{
    use AssertionRenames;

    public function testIfSolutionRequiresComposerButComposerCannotBeLocatedExceptionIsThrown(): void
    {
        $exercise = new CliExerciseImpl();
        $context = new TestContext($exercise);

        $finder = $this->createMock(ExecutableFinder::class);
        $finder->expects($this->once())->method('find')->with('composer')->willReturn(null);

        $solution = $this->createMock(SolutionInterface::class);
        $solution
            ->expects($this->once())
            ->method('hasComposerFile')
            ->willReturn(true);

        $exercise->setSolution($solution);

        $this->expectException(ProcessNotFoundException::class);
        $this->expectExceptionMessage('Could not find executable: "composer"');
        $event = new ExerciseRunnerEvent('event', $context);
        (new PrepareSolutionListener(new HostProcessFactory($finder)))->__invoke($event);
    }

    public function testIfSolutionRequiresComposerButVendorDirExistsNothingIsDone(): void
    {
        $exercise = new CliExerciseImpl();
        $context = new TestContext($exercise);
        $context->createReferenceSolutionDirectory();

        mkdir(sprintf('%s/vendor', $context->getReferenceExecutionDirectory()));
        $this->assertFileExists(sprintf('%s/vendor', $context->getReferenceExecutionDirectory()));

        $solution = $this->createMock(SolutionInterface::class);
        $solution
            ->expects($this->once())
            ->method('hasComposerFile')
            ->willReturn(true);

        $exercise->setSolution($solution);

        $event = new ExerciseRunnerEvent('event', $context);
        (new PrepareSolutionListener(new HostProcessFactory()))->__invoke($event);

        $this->assertFileExists(sprintf('%s/vendor', $context->getReferenceExecutionDirectory()));
        //check for non existence of lock file, composer generates this when updating if it doesn't exist
        $this->assertFileDoesNotExist(sprintf('%s/composer.lock', $context->getReferenceExecutionDirectory()));
    }

    public function testIfSolutionRequiresComposerComposerInstallIsExecuted(): void
    {
        $exercise = new CliExerciseImpl();
        $context = new TestContext($exercise);
        $context->createReferenceSolutionDirectory();
        $context->importReferenceFileFromString(
            json_encode([
                'require' => [
                    'phpunit/phpunit' => '~5.0',
                ],
            ]),
            'composer.json',
        );

        $solution = $this->createMock(SolutionInterface::class);
        $solution
            ->expects($this->once())
            ->method('hasComposerFile')
            ->willReturn(true);

        $exercise->setSolution($solution);

        $event = new ExerciseRunnerEvent('event', $context);
        (new PrepareSolutionListener(new HostProcessFactory()))->__invoke($event);

        $this->assertFileExists(sprintf('%s/vendor', $context->getReferenceExecutionDirectory()));
    }

    public function testExceptionIsThrownIfDependenciesCannotBeResolved(): void
    {
        $exercise = new CliExerciseImpl();
        $context = new TestContext($exercise);

        $this->expectException(\PhpSchool\PhpWorkshop\Exception\RuntimeException::class);
        $this->expectExceptionMessage('Composer dependencies could not be installed');

        $exercise = new CliExerciseImpl();
        $context = new TestContext($exercise);

        $context->createReferenceSolutionDirectory();
        $context->importReferenceFileFromString(
            json_encode([
                'require' => [
                    'phpunit/phpunit' => '1.0',
                ],
            ]),
            'composer.json',
        );

        $solution = $this->createMock(SolutionInterface::class);
        $exercise->setSolution($solution);

        $solution
            ->expects($this->once())
            ->method('hasComposerFile')
            ->willReturn(true);

        $event = new ExerciseRunnerEvent('event', $context);
        (new PrepareSolutionListener(new HostProcessFactory()))->__invoke($event);

        $this->assertFileExists(sprintf('%s/vendor', $context->getReferenceExecutionDirectory()));
    }
}
