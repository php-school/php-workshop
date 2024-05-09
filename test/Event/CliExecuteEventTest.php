<?php

namespace PhpSchool\PhpWorkshopTest\Event;

use PhpSchool\PhpWorkshop\Event\CliExecuteEvent;
use PhpSchool\PhpWorkshop\Exercise\MockExercise;
use PhpSchool\PhpWorkshop\Exercise\Scenario\CgiScenario;
use PhpSchool\PhpWorkshop\Exercise\Scenario\CliScenario;
use PhpSchool\PhpWorkshop\ExerciseRunner\Context\TestContext;
use PhpSchool\PhpWorkshop\Input\Input;
use PhpSchool\PhpWorkshop\Utils\ArrayObject;
use PhpSchool\PhpWorkshop\Utils\Collection;
use PHPUnit\Framework\TestCase;

class CliExecuteEventTest extends TestCase
{
    public function testAppendArg(): void
    {
        $context = new TestContext();
        $scenario = new CliScenario();

        $arr = new Collection([1, 2, 3]);
        $e = new CliExecuteEvent('event', $context, $scenario, $arr);

        $e->appendArg('4');
        $this->assertEquals([1, 2, 3, 4], $e->getArgs()->getArrayCopy());
        $this->assertNotSame($arr, $e->getArgs());
    }

    public function testPrependArg(): void
    {
        $context = new TestContext();
        $scenario = new CliScenario();

        $arr = new Collection([1, 2, 3]);
        $e = new CliExecuteEvent('event', $context, $scenario, $arr);

        $e->prependArg('4');
        $this->assertEquals([4, 1, 2, 3], $e->getArgs()->getArrayCopy());
        $this->assertNotSame($arr, $e->getArgs());
    }

    public function testGetters(): void
    {
        $context = new TestContext();
        $scenario = new CliScenario();

        $arr = new Collection([1, 2, 3]);
        $e = new CliExecuteEvent('event', $context, $scenario, $arr);

        $this->assertSame($arr, $e->getArgs());
        $this->assertSame($context, $e->getContext());
        $this->assertSame($scenario, $e->getScenario());
    }
}
