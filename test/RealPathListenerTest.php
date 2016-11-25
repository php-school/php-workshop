<?php

namespace PhpSchool\PhpWorkshopTest;

use PhpSchool\PhpWorkshop\Event\Event;
use PhpSchool\PhpWorkshop\Event\ExerciseRunnerEvent;
use PhpSchool\PhpWorkshop\Input\Input;
use PhpSchool\PhpWorkshop\Listener\RealPathListener;
use PhpSchool\PhpWorkshopTest\Asset\CliExerciseImpl;
use PHPUnit_Framework_TestCase;

/**
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class RealPathListenerTest extends PHPUnit_Framework_TestCase
{
    public function testInputArgumentIsReplacesWithAbsolutePathIfFileExists()
    {
        $current = getcwd();

        $tempDirectory = sprintf('%s/%s', realpath(sys_get_temp_dir()), $this->getName());
        mkdir($tempDirectory, 0777, true);
        chdir($tempDirectory);
        touch('test-file.php');

        $exercise = new CliExerciseImpl;
        $input = new Input('app', ['program' => 'test-file.php']);
        $listener = new RealPathListener;
        $listener->__invoke(new ExerciseRunnerEvent('some.event', $exercise, $input));

        $this->assertEquals(sprintf('%s/test-file.php', $tempDirectory), $input->getArgument('program'));

        unlink('test-file.php');
        rmdir($tempDirectory);
        chdir($current);
    }

    public function testInputArgumentIsLeftUnchangedIfFileDoesNotExist()
    {
        $exercise = new CliExerciseImpl;
        $input = new Input('app', ['program' => 'test-file.php']);
        $listener = new RealPathListener;
        $listener->__invoke(new ExerciseRunnerEvent('some.event', $exercise, $input));

        $this->assertEquals('test-file.php', $input->getArgument('program'));
    }

    private function runInDir($dir, callable $callback)
    {
        $current = getcwd();
        chdir($dir);
        $callback($dir);
        chdir($current);
    }
}
