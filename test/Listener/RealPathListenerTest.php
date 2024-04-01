<?php

namespace PhpSchool\PhpWorkshopTest\Listener;

use _PHPStan_de1c07ea6\Nette\Schema\Context;
use PhpSchool\PhpWorkshop\Event\Event;
use PhpSchool\PhpWorkshop\Event\ExerciseRunnerEvent;
use PhpSchool\PhpWorkshop\ExerciseRunner\Context\CliContext;
use PhpSchool\PhpWorkshop\ExerciseRunner\Context\ExecutionContext;
use PhpSchool\PhpWorkshop\ExerciseRunner\Context\TestContext;
use PhpSchool\PhpWorkshop\Input\Input;
use PhpSchool\PhpWorkshop\Listener\RealPathListener;
use PhpSchool\PhpWorkshopTest\Asset\CliExerciseImpl;
use PhpSchool\PhpWorkshopTest\BaseTest;
use PHPUnit\Framework\TestCase;

class RealPathListenerTest extends BaseTest
{
    public function testInputArgumentIsReplacesWithAbsolutePathIfFileExists(): void
    {
        $current = getcwd();

        $tempDirectory = $this->getTemporaryDirectory();
        chdir($tempDirectory);

        $this->getTemporaryFile('test-file.php');

        $exercise = new CliExerciseImpl();
        $input = new Input('app', ['program' => 'test-file.php']);
        $listener = new RealPathListener();
        $listener->__invoke(new ExerciseRunnerEvent('some.event', TestContext::withoutEnvironment(input: $input)));

        $this->assertEquals(sprintf('%s/test-file.php', $tempDirectory), $input->getArgument('program'));

        chdir($current);
    }

    public function testInputArgumentIsLeftUnchangedIfFileDoesNotExist(): void
    {
        $exercise = new CliExerciseImpl();
        $input = new Input('app', ['program' => 'test-file.php']);
        $listener = new RealPathListener();
        $listener->__invoke(new ExerciseRunnerEvent('some.event', TestContext::withoutEnvironment(input: $input)));

        $this->assertEquals('test-file.php', $input->getArgument('program'));
    }

    public function testInputIsUnchangedIfNoProgramArgument(): void
    {
        $exercise = new CliExerciseImpl();
        $input = new Input('app', ['some-arg' => 'some-value']);

        $listener = new RealPathListener();
        $listener->__invoke(new ExerciseRunnerEvent('some.event', TestContext::withoutEnvironment(input: $input)));

        $this->assertEquals('some-value', $input->getArgument('some-arg'));
    }
}
