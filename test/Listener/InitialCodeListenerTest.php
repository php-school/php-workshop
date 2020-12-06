<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshopTest\Listener;

use PhpSchool\PhpWorkshop\Event\Event;
use PhpSchool\PhpWorkshop\Listener\InitialCodeListener;
use PhpSchool\PhpWorkshopTest\Asset\CliExerciseImpl;
use PhpSchool\PhpWorkshopTest\Asset\ExerciseWithInitialCode;
use PhpSchool\PhpWorkshopTest\ContainerAwareTest;

class InitialCodeListenerTest extends ContainerAwareTest
{
    public function setUp(): void
    {
        parent::setUp();

        $this->mockCurrentWorkingDirectory();
        $this->mockLogger();
    }

    public function testExerciseCodeIsCopiedIfExerciseProvidesInitialCode(): void
    {
        $exercise = new ExerciseWithInitialCode();

        $event = new Event('exercise.selected', ['exercise' => $exercise]);

        $listener = $this->container->get(InitialCodeListener::class);
        $listener->__invoke($event);

        $this->assertFileExists($this->getCurrentWorkingDirectory() . '/init-solution.php');
        $this->assertFileEquals(
            $exercise->getInitialCode()->getFiles()[0]->getAbsolutePath(),
            $this->getCurrentWorkingDirectory() . '/init-solution.php'
        );

        $this->assertLoggerHasMessages(
            [
                [
                    'level' => 'debug',
                    'message' => 'File successfully copied to working directory',
                    'context' => [
                        'exercise' => 'exercise-with-initial-code',
                        'workingDir' => $this->getCurrentWorkingDirectory(),
                        'file' => $exercise->getInitialCode()->getFiles()[0]->getAbsolutePath()
                    ]
                ]
            ]
        );
    }

    public function testExerciseCodeIsNotCopiedIfFileWithSameNameExistsInWorkingDirectory(): void
    {
        $exercise = new ExerciseWithInitialCode();

        $event = new Event('exercise.selected', ['exercise' => $exercise]);

        touch($this->getCurrentWorkingDirectory() . '/init-solution.php');

        $listener = $this->container->get(InitialCodeListener::class);
        $listener->__invoke($event);

        $this->assertFileExists($this->getCurrentWorkingDirectory() . '/init-solution.php');

        $this->assertLoggerHasMessages(
            [
                [
                    'level' => 'debug',
                    'message' => 'File not copied. File with same name already exists in working directory',
                    'context' => [
                        'exercise' => 'exercise-with-initial-code',
                        'workingDir' => $this->getCurrentWorkingDirectory(),
                        'file' => $exercise->getInitialCode()->getFiles()[0]->getAbsolutePath()
                    ]
                ]
            ]
        );
    }

    public function testExerciseCodeIsNotCopiedIfExerciseDoesNotProvideInitialCode(): void
    {
        $exercise = new CliExerciseImpl();

        $event = new Event('exercise.selected', ['exercise' => $exercise]);

        $listener = $this->container->get(InitialCodeListener::class);
        $listener->__invoke($event);

        $this->assertEmpty(array_diff(scandir($this->getCurrentWorkingDirectory()), ['.', '..']));
    }
}
