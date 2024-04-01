<?php

namespace PhpSchool\PhpWorkshopTest\ExerciseRunner;

use Colors\Color;
use PhpSchool\PhpWorkshop\Check\CodeExistsCheck;
use PhpSchool\PhpWorkshop\ExerciseRunner\Context\CliContext;
use PhpSchool\PhpWorkshop\ExerciseRunner\Context\ExecutionContext;
use PhpSchool\PhpWorkshop\ExerciseRunner\Context\TestExecutionContext;
use PhpSchool\PhpWorkshop\Listener\OutputRunInfoListener;
use PhpSchool\PhpWorkshop\Process\HostProcessFactory;
use PhpSchool\PhpWorkshop\Utils\RequestRenderer;
use PhpSchool\Terminal\Terminal;
use PhpSchool\PhpWorkshop\Check\CodeParseCheck;
use PhpSchool\PhpWorkshop\Check\FileExistsCheck;
use PhpSchool\PhpWorkshop\Check\PhpLintCheck;
use PhpSchool\PhpWorkshop\Event\CliExecuteEvent;
use PhpSchool\PhpWorkshop\Event\EventDispatcher;
use PhpSchool\PhpWorkshop\Exception\SolutionExecutionException;
use PhpSchool\PhpWorkshop\Exercise\ExerciseType;
use PhpSchool\PhpWorkshop\ExerciseRunner\CliRunner;
use PhpSchool\PhpWorkshop\Input\Input;
use PhpSchool\PhpWorkshop\Output\StdOutput;
use PhpSchool\PhpWorkshop\Result\Cli\CliResult;
use PhpSchool\PhpWorkshop\Result\Cli\GenericFailure;
use PhpSchool\PhpWorkshop\Result\Cli\RequestFailure;
use PhpSchool\PhpWorkshop\ResultAggregator;
use PhpSchool\PhpWorkshop\Solution\SingleFileSolution;
use PhpSchool\PhpWorkshopTest\Asset\CliExerciseInterface;
use PHPUnit\Framework\TestCase;
use Yoast\PHPUnitPolyfills\Polyfills\AssertionRenames;

class CliRunnerTest extends TestCase
{
    use AssertionRenames;

    /** @var  CliRunner */
    private $runner;

    /**
     * @var CliExerciseInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $exercise;

    /**
     * @var EventDispatcher
     */
    private $eventDispatcher;

    public function setUp(): void
    {
        $this->exercise = $this->createMock(CliExerciseInterface::class);
        $this->eventDispatcher = new EventDispatcher(new ResultAggregator());
        $this->runner = new CliRunner($this->exercise, $this->eventDispatcher, new HostProcessFactory());

        $this->exercise
            ->method('getType')
            ->willReturn(ExerciseType::CLI());

        $this->assertEquals('CLI Program Runner', $this->runner->getName());
    }

    public function testRequiredChecks(): void
    {
        $requiredChecks = [
            FileExistsCheck::class,
            CodeExistsCheck::class,
            PhpLintCheck::class,
            CodeParseCheck::class,
        ];

        $this->assertEquals($requiredChecks, $this->runner->getRequiredChecks());
    }

    public function testVerifyThrowsExceptionIfSolutionFailsExecution(): void
    {
        $solution = SingleFileSolution::fromFile(realpath(__DIR__ . '/../res/cli/solution-error.php'));
        $this->exercise
            ->method('getSolution')
            ->willReturn($solution);

        $this->exercise
            ->expects($this->once())
            ->method('getArgs')
            ->willReturn([[]]);

        $regex  = "/^PHP Code failed to execute\\. Error: \"PHP Parse error:  syntax error, unexpected end of file";
        $regex .= ", expecting ['\"][,;]['\"] or ['\"][;,]['\"]/";
        $this->expectException(SolutionExecutionException::class);
        $this->expectExceptionMessageMatches($regex);

        $context = new CliContext(
            ExecutionContext::fromInputAndExercise(new Input('app', ['program' => '']), $this->exercise)
        );
        $this->runner->verify($context);
    }

    public function testVerifyReturnsSuccessIfSolutionOutputMatchesUserOutput(): void
    {
        $solution = SingleFileSolution::fromFile(realpath(__DIR__ . '/../res/cli/solution.php'));
        $this->exercise
            ->method('getSolution')
            ->willReturn($solution);

        $this->exercise
            ->expects($this->once())
            ->method('getArgs')
            ->willReturn([[1, 2, 3]]);


        $context = new CliContext(
            ExecutionContext::fromInputAndExercise(
                new Input('app', ['program' => __DIR__ . '/../res/cli/user.php']),
                $this->exercise
            )
        );

        $result = $this->runner->verify($context);

        $this->assertInstanceOf(CliResult::class, $result);

        $this->assertTrue($result->isSuccessful());
    }

    public function testVerifyReturnsFailureIfUserSolutionFailsToExecute(): void
    {
        $solution = SingleFileSolution::fromFile(realpath(__DIR__ . '/../res/cli/solution.php'));
        $this->exercise
            ->method('getSolution')
            ->willReturn($solution);

        $this->exercise
            ->expects($this->once())
            ->method('getArgs')
            ->willReturn([[1, 2, 3]]);

        $context = new CliContext(
            ExecutionContext::fromInputAndExercise(
                new Input('app', ['program' => __DIR__ . '/../res/cli/user-error.php']),
                $this->exercise
            )
        );

        $result = $this->runner->verify($context);

        $failureMsg  = '/^PHP Code failed to execute. Error: "PHP Parse error:  syntax error, ';
        $failureMsg .= "unexpected end of file, expecting ['\"][,;]['\"] or ['\"][;,]['\"]/";

        $this->assertInstanceOf(CliResult::class, $result);
        $this->assertCount(1, $result);

        $result = iterator_to_array($result)[0];
        $this->assertInstanceOf(GenericFailure::class, $result);
        $this->assertMatchesRegularExpression($failureMsg, $result->getReason());
    }

    public function testVerifyReturnsFailureIfSolutionOutputDoesNotMatchUserOutput(): void
    {
        $solution = SingleFileSolution::fromFile(realpath(__DIR__ . '/../res/cli/solution.php'));
        $this->exercise
            ->method('getSolution')
            ->willReturn($solution);

        $this->exercise
            ->expects($this->once())
            ->method('getArgs')
            ->willReturn([[1, 2, 3]]);

        $context = new CliContext(
            ExecutionContext::fromInputAndExercise(
                new Input('app', ['program' => __DIR__ . '/../res/cli/user-wrong.php']),
                $this->exercise
            )
        );

        $result = $this->runner->verify($context);

        $this->assertInstanceOf(CliResult::class, $result);
        $this->assertCount(1, $result);

        $result = iterator_to_array($result)[0];
        $this->assertInstanceOf(RequestFailure::class, $result);

        $this->assertEquals('6', $result->getExpectedOutput());
        $this->assertEquals('10', $result->getActualOutput());
    }

    public function testRunPassesOutputAndReturnsSuccessIfScriptIsSuccessful(): void
    {
        $color = new Color();
        $color->setForceStyle(true);
        $output = new StdOutput($color, $this->createMock(Terminal::class));

        $this->eventDispatcher->listen(
            'cli.run.student-execute.pre',
            new OutputRunInfoListener($output, new RequestRenderer())
        );

        $this->exercise
            ->expects($this->once())
            ->method('getArgs')
            ->willReturn([[1, 2, 3], [4, 5, 6]]);

        $exp  = "\n\e[1m\e[4mArguments\e[0m\e[0m\n";
        $exp .= "1, 2, 3\n";
        $exp .= "\n\e[1m\e[4mOutput\e[0m\e[0m\n\n";
        $exp .= "6\n";
        $exp .= "\e[33m\e[0m\n";
        $exp .= "\e[1m\e[4mArguments\e[0m\e[0m\n";
        $exp .= "4, 5, 6\n\n";
        $exp .= "\e[1m\e[4mOutput\e[0m\e[0m\n\n";
        $exp .= "15\n";
        $exp .= "\e[33m\e[0m";

        $this->expectOutputString($exp);

        $context = new CliContext(
            ExecutionContext::fromInputAndExercise(
                new Input('app', ['program' => __DIR__ . '/../res/cli/user.php']),
                $this->exercise
            )
        );

        $result = $this->runner->run($context, $output);

        $this->assertTrue($result);
    }

    public function testRunPassesOutputAndReturnsFailureIfScriptFails(): void
    {
        $output = new StdOutput(new Color(), $this->createMock(Terminal::class));

        $this->exercise
            ->expects($this->once())
            ->method('getArgs')
            ->willReturn([[1, 2, 3]]);

        $this->expectOutputRegex(
            "/(PHP )?Parse error:\W+syntax error, unexpected end of file, expecting ['\"][,;]['\"] or ['\"][;,]['\"] /"
        );

        $context = new CliContext(
            ExecutionContext::fromInputAndExercise(
                new Input('app', ['program' => __DIR__ . '/../res/cli/user-error.php']),
                $this->exercise
            )
        );

        $result = $this->runner->run($context, $output);

        $this->assertFalse($result);
    }

    public function testsArgsAppendedByEventsArePassedToResults(): void
    {
        $this->eventDispatcher->listen(
            ['cli.verify.student-execute.pre', 'cli.verify.reference-execute.pre'],
            function (CliExecuteEvent $e) {
                $e->appendArg('4');
            }
        );

        $solution = SingleFileSolution::fromFile(realpath(__DIR__ . '/../res/cli/solution.php'));
        $this->exercise
            ->method('getSolution')
            ->willReturn($solution);

        $this->exercise
            ->expects($this->once())
            ->method('getArgs')
            ->willReturn([[1, 2, 3]]);


        $context = new CliContext(
            ExecutionContext::fromInputAndExercise(
                new Input('app', ['program' => __DIR__ . '/../res/cli/user.php']),
                $this->exercise
            )
        );

        $result = $this->runner->verify($context);

        $this->assertInstanceOf(
            CliResult::class,
            $result
        );

        $this->assertTrue($result->isSuccessful());
        $this->assertEquals([1, 2, 3, 4], $result->getResults()[0]->getArgs()->getArrayCopy());
    }
}
