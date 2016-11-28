<?php

namespace PhpSchool\PhpWorkshop\ExerciseRunner;

use Colors\Color;
use PhpSchool\CliMenu\Terminal\TerminalInterface;
use PhpSchool\PhpWorkshop\Input\Input;
use PhpSchool\PhpWorkshop\Output\StdOutput;
use PhpSchool\PhpWorkshopTest\Asset\CustomVerifyingExerciseImpl;
use PHPUnit_Framework_TestCase;

/**
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class ExtRunnerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var CustomVerifyingRunner
     */
    private $runner;

    /**
     * @var CustomVerifyingExerciseImpl
     */
    private $exercise;

    public function setUp()
    {
        $this->exercise = new CustomVerifyingExerciseImpl;
        $this->runner = new CustomVerifyingRunner($this->exercise);

        $this->assertEquals('Custom Verifying Runner', $this->runner->getName());
    }

    public function testRequiredChecks()
    {
        $this->assertEquals([], $this->runner->getRequiredChecks());
    }

    public function testRunOutputsErrorMessage()
    {
        $color = new Color;
        $color->setForceStyle(true);
        $output = new StdOutput($color, $this->createMock(TerminalInterface::class));

        $exp  = 'Nothing to run here. This exercise does not require a code solution, ';
        $exp .= "so there is nothing to execute.\n";

        $this->expectOutputString($exp);

        $this->runner->run(new Input('app'), $output);
    }

    public function testVerifyProxiesToExercise()
    {
        self::assertEquals($this->exercise->verify(), $this->runner->verify(new Input('app')));
    }
}
