<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshopTest\Listener;

use PhpSchool\PhpWorkshop\Event\Event;
use PhpSchool\PhpWorkshop\Listener\InitialCodeListener;
use PhpSchool\PhpWorkshopTest\Asset\CliExerciseImpl;
use PhpSchool\PhpWorkshopTest\Asset\ExerciseWithInitialCode;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

class InitialCodeListenerTest extends TestCase
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var string
     */
    private $cwd;

    public function setUp(): void
    {
        $this->filesystem = new Filesystem();

        $this->cwd = sprintf('%s/%s', str_replace('\\', '/', sys_get_temp_dir()), $this->getName());
        mkdir($this->cwd, 0775, true);
    }

    public function testExerciseCodeIsCopiedIfExerciseProvidesInitialCode(): void
    {
        $exercise = new ExerciseWithInitialCode();

        $event = new Event('exercise.selected', ['exercise' => $exercise]);

        $listener = new InitialCodeListener($this->cwd);
        $listener->__invoke($event);

        $this->assertFileExists($this->cwd . '/init-solution.php');
        $this->assertFileEquals(
            $exercise->getInitialCode()->getFiles()[0]->getAbsolutePath(),
            $this->cwd . '/init-solution.php'
        );
    }

    public function testExerciseCodeIsNotCopiedIfExerciseDoesNotProvideInitialCode(): void
    {
        $exercise = new CliExerciseImpl();

        $event = new Event('exercise.selected', ['exercise' => $exercise]);

        $listener = new InitialCodeListener($this->cwd);
        $listener->__invoke($event);

        $this->assertEmpty(array_diff(scandir($this->cwd), ['.', '..']));
    }

    public function tearDown(): void
    {
        $this->filesystem->remove($this->cwd);
    }
}
