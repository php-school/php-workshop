<?php

namespace PhpSchool\PhpWorkshop\ExerciseRunner;

use Colors\Color;
use PhpSchool\CliMenu\Terminal\TerminalInterface;
use PhpSchool\PhpWorkshop\ExerciseDispatcher;
use PhpSchool\PhpWorkshop\Input\Input;
use PhpSchool\PhpWorkshop\Output\StdOutput;
use PhpSchool\PhpWorkshopTest\Asset\ExtExerciseImpl;
use PHPUnit_Framework_TestCase;

/**
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class ExtRunnerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var CustomRunner
     */
    private $runner;

    /**
     * @var ExtExerciseImpl
     */
    private $exercise;

    public function setUp()
    {
        $this->exercise = new ExtExerciseImpl;
        $this->runner = new CustomRunner($this->exercise);

        $this->assertEquals('External Runner', $this->runner->getName());
    }

    public function testRunOutputsErrorMessage()
    {
        $color = new Color;
        $color->setForceStyle(true);
        $output = new StdOutput($color, $this->createMock(TerminalInterface::class));

        $exp  = "Nothing to run here. This exercise does not require a code solution, ";
        $exp .= "so there is nothing to execute.\n";

        $this->expectOutputString($exp);

        $this->runner->run(new Input('app'), $output);
    }

    public function testVerifyProxiesToExercise()
    {
        self::assertEquals($this->exercise->verify(), $this->runner->verify(new Input('app')));
    }
}
