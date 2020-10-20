<?php

namespace PhpSchool\PhpWorkshopTest\ExerciseRunner;

use Colors\Color;
use PhpSchool\Terminal\Terminal;
use PhpSchool\PhpWorkshop\ExerciseRunner\CustomVerifyingRunner;
use PhpSchool\PhpWorkshop\Input\Input;
use PhpSchool\PhpWorkshop\Output\StdOutput;
use PhpSchool\PhpWorkshopTest\Asset\CustomVerifyingExerciseImpl;
use PHPUnit\Framework\TestCase;

class CustomVerifyingRunnerTest extends TestCase
{
    /**
     * @var CustomVerifyingRunner
     */
    private $runner;

    /**
     * @var CustomVerifyingExerciseImpl
     */
    private $exercise;

    public function setUp(): void
    {
        $this->exercise = new CustomVerifyingExerciseImpl();
        $this->runner = new CustomVerifyingRunner($this->exercise);

        $this->assertEquals('Custom Verifying Runner', $this->runner->getName());
    }

    public function testRequiredChecks(): void
    {
        $this->assertEquals([], $this->runner->getRequiredChecks());
    }

    public function testRunOutputsErrorMessage(): void
    {
        $color = new Color();
        $color->setForceStyle(true);
        $output = new StdOutput($color, $this->createMock(Terminal::class));

        $exp  = 'Nothing to run here. This exercise does not require a code solution, ';
        $exp .= "so there is nothing to execute.\n";

        $this->expectOutputString($exp);

        $this->runner->run(new Input('app'), $output);
    }

    public function testVerifyProxiesToExercise(): void
    {
        self::assertEquals($this->exercise->verify(), $this->runner->verify(new Input('app')));
    }
}
