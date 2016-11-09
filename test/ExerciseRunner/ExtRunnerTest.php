<?php

namespace PhpSchool\PhpWorkshop\ExerciseRunner;

use Colors\Color;
use PhpSchool\CliMenu\Terminal\TerminalInterface;
use PhpSchool\PhpWorkshop\Event\EventDispatcher;
use PhpSchool\PhpWorkshop\Exception\SolutionExecutionException;
use PhpSchool\PhpWorkshop\Exercise\ExerciseType;
use PhpSchool\PhpWorkshop\ExerciseDispatcher;
use PhpSchool\PhpWorkshop\Output\StdOutput;
use PhpSchool\PhpWorkshop\Result\CgiOutRequestFailure;
use PhpSchool\PhpWorkshop\Result\CgiOutResult;
use PhpSchool\PhpWorkshop\Result\Failure;
use PhpSchool\PhpWorkshop\ResultAggregator;
use PhpSchool\PhpWorkshop\Solution\SingleFileSolution;
use PhpSchool\PhpWorkshopTest\Asset\CgiExerciseInterface;
use PhpSchool\PhpWorkshopTest\Asset\ExtExerciseImpl;
use PHPUnit_Framework_TestCase;
use Zend\Diactoros\Request;
use Zend\Diactoros\Uri;

/**
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class ExtRunnerTest extends PHPUnit_Framework_TestCase
{
    /** @var  ExtRunner */
    private $runner;

    /**
     * @var ExtExerciseImpl
     */
    private $exercise;

    public function setUp()
    {
        $this->exercise = new ExtExerciseImpl;
        $this->runner = new ExtRunner($this->exercise);

        $this->assertEquals('External Runner', $this->runner->getName());
    }

    public function testConfigure()
    {
        $exerciseDispatcher = $this->createMock(ExerciseDispatcher::class);
        self::assertSame($this->runner, $this->runner->configure($exerciseDispatcher));
    }

    public function testRunOutputsErrorMessage()
    {
        $color = new Color;
        $color->setForceStyle(true);
        $output = new StdOutput($color, $this->createMock(TerminalInterface::class));

        $exp  = "Nothing to run here. This exercise does not require a code solution, ";
        $exp .= "so there is nothing to execute.\n";

        $this->expectOutputString($exp);

        $this->runner->run(null, $output);
    }

    public function testVerifyProxiesToExercise()
    {
        self::assertEquals($this->exercise->verify(), $this->runner->verify());
    }
}
