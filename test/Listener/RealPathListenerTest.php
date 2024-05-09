<?php

namespace PhpSchool\PhpWorkshopTest\Listener;

use PhpSchool\PhpWorkshop\Event\Event;
use PhpSchool\PhpWorkshop\Event\ExerciseRunnerEvent;
use PhpSchool\PhpWorkshop\ExerciseRunner\Context\TestContext;
use PhpSchool\PhpWorkshop\ExerciseRunner\Context\ExecutionContext;
use PhpSchool\PhpWorkshop\Input\Input;
use PhpSchool\PhpWorkshop\Listener\RealPathListener;
use PhpSchool\PhpWorkshopTest\Asset\CliExerciseImpl;
use PhpSchool\PhpWorkshopTest\BaseTest;
use PHPUnit\Framework\TestCase;

class RealPathListenerTest extends BaseTest
{
    public function testInputArgumentIsReplacesWithAbsolutePathIfFileExists(): void
    {
        $exercise = new CliExerciseImpl();
        $input = new Input('app', ['program' => 'test-file.php']);
        $listener = new RealPathListener();

        $context = new TestContext(input: $input);
        $context->createStudentSolutionDirectory();
        $context->importStudentFileFromString('', 'test-file.php');

        $current = getcwd();
        chdir($context->getStudentExecutionDirectory());

        $listener->__invoke(new ExerciseRunnerEvent('some.event', $context));

        $this->assertEquals(sprintf('%s/test-file.php', $context->getStudentExecutionDirectory()), $input->getArgument('program'));

        chdir($current);
    }

    public function testInputArgumentIsLeftUnchangedIfFileDoesNotExist(): void
    {
        $exercise = new CliExerciseImpl();
        $input = new Input('app', ['program' => 'test-file.php']);
        $listener = new RealPathListener();

        $context = new TestContext(input: $input);
        $context->createStudentSolutionDirectory();

        $current = getcwd();
        chdir($context->getStudentExecutionDirectory());

        $listener->__invoke(new ExerciseRunnerEvent('some.event', $context));

        $this->assertEquals('test-file.php', $input->getArgument('program'));

        chdir($current);
    }

    public function testInputIsUnchangedIfNoProgramArgument(): void
    {
        $exercise = new CliExerciseImpl();
        $input = new Input('app', ['some-arg' => 'some-value']);

        $listener = new RealPathListener();

        $context = new TestContext(input: $input);
        $context->createStudentSolutionDirectory();

        $current = getcwd();
        chdir($context->getStudentExecutionDirectory());

        $listener->__invoke(new ExerciseRunnerEvent('some.event', $context));

        $this->assertEquals('some-value', $input->getArgument('some-arg'));

        chdir($current);
    }
}
