<?php

namespace PhpSchool\PhpWorkshopTest\Listener;

use PhpSchool\PhpWorkshop\CodePatcher;
use PhpSchool\PhpWorkshop\Event\Event;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\Listener\CodePatchListener;
use PHPUnit_Framework_TestCase;
use RuntimeException;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class CodePatchListenerTest
 * @package PhpSchool\PhpWorkshopTest\Listener
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class CodePatchListenerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    private $file;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var CodePatcher
     */
    private $codePatcher;

    public function setUp()
    {
        $this->filesystem = new Filesystem;
        $this->codePatcher = $this->getMockBuilder(CodePatcher::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->file = sprintf('%s/%s/submission.php', str_replace('\\', '/', sys_get_temp_dir()), $this->getName());
        mkdir(dirname($this->file), 0775, true);
        touch($this->file);
    }

    public function testRevertThrowsExceptionIfPatchNotPreviouslyCalled()
    {
        $fileName = $this->file;
        $exercise = $this->getMock(ExerciseInterface::class);

        $listener   = new CodePatchListener($this->codePatcher);
        $event      = new Event('event', compact('exercise', 'fileName'));

        $this->setExpectedException(RuntimeException::class, 'Can only revert previously patched code');
        $listener->revert($event);
    }

    public function testPatchUpdatesCode()
    {
        file_put_contents($this->file, 'ORIGINAL CONTENT');

        $fileName = $this->file;
        $exercise = $this->getMock(ExerciseInterface::class);

        $this->codePatcher
            ->expects($this->once())
            ->method('patch')
            ->with($exercise, 'ORIGINAL CONTENT')
            ->will($this->returnValue('MODIFIED CONTENT'));

        $listener   = new CodePatchListener($this->codePatcher);
        $event      = new Event('event', compact('exercise', 'fileName'));
        $listener->patch($event);

        $this->assertStringEqualsFile($this->file, 'MODIFIED CONTENT');
    }

    public function testRevertAfterPatch()
    {
        file_put_contents($this->file, 'ORIGINAL CONTENT');

        $fileName = $this->file;
        $exercise = $this->getMock(ExerciseInterface::class);

        $this->codePatcher
            ->expects($this->once())
            ->method('patch')
            ->with($exercise, 'ORIGINAL CONTENT')
            ->will($this->returnValue('MODIFIED CONTENT'));

        $listener   = new CodePatchListener($this->codePatcher);
        $event      = new Event('event', compact('exercise', 'fileName'));
        $listener->patch($event);
        $listener->revert($event);

        $this->assertStringEqualsFile($this->file, 'ORIGINAL CONTENT');
    }

    public function tearDown()
    {
        $this->filesystem->remove(dirname($this->file));
    }
}
