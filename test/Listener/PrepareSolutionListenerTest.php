<?php

namespace PhpSchool\PhpWorkshopTest\Listener;

use PhpSchool\PhpWorkshop\Event\ExerciseRunnerEvent;
use PhpSchool\PhpWorkshop\Exercise\CliExercise;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\Input\Input;
use PhpSchool\PhpWorkshop\Listener\PrepareSolutionListener;
use PhpSchool\PhpWorkshop\Solution\SolutionInterface;
use PhpSchool\PhpWorkshopTest\Asset\CliExerciseInterface;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use RuntimeException;
use Symfony\Component\Filesystem\Filesystem;
use Yoast\PHPUnitPolyfills\Polyfills\AssertionRenames;

class PrepareSolutionListenerTest extends TestCase
{
    use AssertionRenames;

    /**
     * @var string
     */
    private $file;

    /**
     * @var PrepareSolutionListener
     */
    private $listener;

    /**
     * @var Filesystem
     */
    private $filesystem;

    public function setUp(): void
    {
        $this->filesystem = new Filesystem();
        $this->listener = new PrepareSolutionListener();
        $this->file = sprintf('%s/%s/submission.php', str_replace('\\', '/', sys_get_temp_dir()), $this->getName());

        mkdir(dirname($this->file), 0775, true);
        touch($this->file);
    }

    /**
     * @runInSeparateProcess
     */
    public function testIfSolutionRequiresComposerButComposerCannotBeLocatedExceptionIsThrown(): void
    {
        $refProp = new ReflectionProperty(PrepareSolutionListener::class, 'composerLocations');
        $refProp->setAccessible(true);
        $refProp->setValue($this->listener, []);

        $solution = $this->createMock(SolutionInterface::class);
        $exercise = $this->createMock(CliExerciseInterface::class);
        $exercise
            ->method('getSolution')
            ->willReturn($solution);

        $solution
            ->expects($this->once())
            ->method('hasComposerFile')
            ->willReturn(true);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Composer could not be located on the system');
        $event = new ExerciseRunnerEvent('event', $exercise, new Input('app'));
        $this->listener->__invoke($event);
    }

    public function testIfSolutionRequiresComposerButVendorDirExistsNothingIsDone(): void
    {
        mkdir(sprintf('%s/vendor', dirname($this->file)));
        $this->assertFileExists(sprintf('%s/vendor', dirname($this->file)));

        $solution = $this->createMock(SolutionInterface::class);
        $exercise = $this->createMock(CliExerciseInterface::class);
        $exercise
            ->method('getSolution')
            ->willReturn($solution);

        $solution
            ->expects($this->once())
            ->method('hasComposerFile')
            ->willReturn(true);

        $solution
            ->method('getBaseDirectory')
            ->willReturn(dirname($this->file));

        $event = new ExerciseRunnerEvent('event', $exercise, new Input('app'));
        $this->listener->__invoke($event);

        $this->assertFileExists(sprintf('%s/vendor', dirname($this->file)));
        //check for non existence of lock file, composer generates this when updating if it doesn't exist
        $this->assertFileDoesNotExist(sprintf('%s/composer.lock', dirname($this->file)));
    }

    public function testIfSolutionRequiresComposerComposerInstallIsExecuted(): void
    {
        $this->assertFileDoesNotExist(sprintf('%s/vendor', dirname($this->file)));
        file_put_contents(sprintf('%s/composer.json', dirname($this->file)), json_encode([
            'require' => [
                'phpunit/phpunit' => '~5.0'
            ],
        ]));

        $solution = $this->createMock(SolutionInterface::class);
        $exercise = $this->createMock(CliExerciseInterface::class);
        $exercise
            ->method('getSolution')
            ->willReturn($solution);

        $solution
            ->expects($this->once())
            ->method('hasComposerFile')
            ->willReturn(true);

        $solution
            ->method('getBaseDirectory')
            ->willReturn(dirname($this->file));

        $event = new ExerciseRunnerEvent('event', $exercise, new Input('app'));
        $this->listener->__invoke($event);

        $this->assertFileExists(sprintf('%s/vendor', dirname($this->file)));
    }

    public function testExceptionIsThrownIfDependenciesCannotBeResolved(): void
    {
        $this->expectException(\PhpSchool\PhpWorkshop\Exception\RuntimeException::class);
        $this->expectExceptionMessage('Composer dependencies could not be installed');

        $this->assertFileDoesNotExist(sprintf('%s/vendor', dirname($this->file)));
        file_put_contents(sprintf('%s/composer.json', dirname($this->file)), json_encode([
            'require' => [
                'phpunit/phpunit' => '1.0'
            ],
        ]));

        $solution = $this->createMock(SolutionInterface::class);
        $exercise = $this->createMock(CliExerciseInterface::class);
        $exercise
            ->method('getSolution')
            ->willReturn($solution);

        $solution
            ->expects($this->once())
            ->method('hasComposerFile')
            ->willReturn(true);

        $solution
            ->method('getBaseDirectory')
            ->willReturn(dirname($this->file));

        $event = new ExerciseRunnerEvent('event', $exercise, new Input('app'));
        $this->listener->__invoke($event);

        $this->assertFileExists(sprintf('%s/vendor', dirname($this->file)));
    }

    public function tearDown(): void
    {
        $this->filesystem->remove(dirname($this->file));
    }
}
