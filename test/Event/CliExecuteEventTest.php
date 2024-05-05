<?php

namespace PhpSchool\PhpWorkshopTest\Event;

use PhpSchool\PhpWorkshop\Environment\CliTestEnvironment;
use PhpSchool\PhpWorkshop\Event\CliExecuteEvent;
use PhpSchool\PhpWorkshop\ExerciseRunner\Context\CliContext;
use PhpSchool\PhpWorkshop\ExerciseRunner\Context\ExecutionContext;
use PhpSchool\PhpWorkshop\ExerciseRunner\Context\TestContext;
use PhpSchool\PhpWorkshop\Input\Input;
use PhpSchool\PhpWorkshop\Utils\ArrayObject;
use PhpSchool\PhpWorkshop\Utils\Collection;
use PhpSchool\PhpWorkshopTest\Asset\CliExerciseImpl;
use PHPUnit\Framework\TestCase;

class CliExecuteEventTest extends TestCase
{
    public function testAppendArg(): void
    {
        $context = new ExecutionContext('', '', new CliExerciseImpl(), new Input('test', []));

        $arr = new Collection([1, 2, 3]);
        $e = new CliExecuteEvent('event', $context, new CliTestEnvironment(), $arr);

        $e->appendArg('4');
        $this->assertEquals([1, 2, 3, 4], $e->getArgs()->getArrayCopy());
        $this->assertNotSame($arr, $e->getArgs());
    }

    public function testPrependArg(): void
    {
        $context = new ExecutionContext('', '', new CliExerciseImpl(), new Input('test', []));

        $arr = new Collection([1, 2, 3]);
        $e = new CliExecuteEvent('event', $context, new CliTestEnvironment(), $arr);

        $e->prependArg('4');
        $this->assertEquals([4, 1, 2, 3], $e->getArgs()->getArrayCopy());
        $this->assertNotSame($arr, $e->getArgs());
    }

    public function testGetArgs(): void
    {
        $context = new ExecutionContext('', '', new CliExerciseImpl(), new Input('test', []));

        $arr = new Collection([1, 2, 3]);
        $e = new CliExecuteEvent('event', $context, new CliTestEnvironment(), $arr);

        $this->assertSame($arr, $e->getArgs());
    }
}
