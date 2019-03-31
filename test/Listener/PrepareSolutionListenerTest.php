<?php

namespace PhpSchool\PhpWorkshopTest\Listener;

use PhpSchool\PhpWorkshop\Event\ExerciseRunnerEvent;
use PhpSchool\PhpWorkshop\Exercise\CliExercise;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\Input\Input;
use PhpSchool\PhpWorkshop\Listener\PrepareSolutionListener;
use PhpSchool\PhpWorkshop\Solution\SolutionInterface;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use RuntimeException;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class PrepareSolutionListenerTest
 * @package PhpSchool\PhpWorkshopTest\Listener
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class PrepareSolutionListenerTest extends TestCase
{
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

    public function setUp()
    {
        $this->filesystem = new Filesystem;
        $this->listener = new PrepareSolutionListener;
        $this->file = sprintf('%s/%s/submission.php', str_replace('\\', '/', sys_get_temp_dir()), $this->getName());

        mkdir(dirname($this->file), 0775, true);
        touch($this->file);
    }

    public function testIfSolutionRequiresComposerButComposerCannotBeLocatedExceptionIsThrown()
    {
        $refProp = new ReflectionProperty(PrepareSolutionListener::class, 'composerLocations');
        $refProp->setAccessible(true);
        $refProp->setValue($this->listener, []);

        $solution = $this->createMock(SolutionInterface::class);
        $exercise = $this->createMock([ExerciseInterface::class, CliExercise::class]);
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

    public function testIfSolutionRequiresComposerButVendorDirExistsNothingIsDone()
    {
        mkdir(sprintf('%s/vendor', dirname($this->file)));
        $this->assertFileExists(sprintf('%s/vendor', dirname($this->file)));

        $solution = $this->createMock(SolutionInterface::class);
        $exercise = $this->createMock([ExerciseInterface::class, CliExercise::class]);
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
        $this->assertFileNotExists(sprintf('%s/composer.lock', dirname($this->file)));
    }

    public function testIfSolutionRequiresComposerComposerInstallIsExecuted()
    {
        $this->assertFileNotExists(sprintf('%s/vendor', dirname($this->file)));
        file_put_contents(sprintf('%s/composer.json', dirname($this->file)), json_encode([
            'requires' => [
                'phpunit/phpunit' => '~5.0'
            ],
        ]));

        $solution = $this->createMock(SolutionInterface::class);
        $exercise = $this->createMock([ExerciseInterface::class, CliExercise::class]);
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

    public function tearDown()
    {
        $this->filesystem->remove(dirname($this->file));
    }
}
