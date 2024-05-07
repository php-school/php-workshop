<?php

namespace PhpSchool\PhpWorkshopTest\ExerciseRunner;

use Colors\Color;
use PhpSchool\PhpWorkshop\Check\CodeExistsCheck;
use PhpSchool\PhpWorkshop\Exercise\Scenario\CliScenario;
use PhpSchool\PhpWorkshop\ExerciseRunner\Context\TestContext;
use PhpSchool\PhpWorkshop\Listener\OutputRunInfoListener;
use PhpSchool\PhpWorkshop\Process\HostProcessFactory;
use PhpSchool\PhpWorkshop\Utils\RequestRenderer;
use PhpSchool\PhpWorkshopTest\Asset\CliExerciseImpl;
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
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Yoast\PHPUnitPolyfills\Polyfills\AssertionRenames;

class CliRunnerTest extends TestCase
{
    use AssertionRenames;

    private CliRunner $runner;
    private CliExerciseImpl $exercise;
    private EventDispatcher $eventDispatcher;

    public function setUp(): void
    {
        $this->exercise = new CliExerciseImpl();
        $this->eventDispatcher = new EventDispatcher(new ResultAggregator());
        $this->runner = new CliRunner($this->exercise, $this->eventDispatcher, new HostProcessFactory());

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

        $this->exercise->setSolution($solution);
        $this->exercise->setScenario((new CliScenario())->withExecution());

        $regex  = "/^PHP Code failed to execute\\. Error: \"PHP Parse error:  syntax error, unexpected end of file";
        $regex .= ", expecting ['\"][,;]['\"] or ['\"][;,]['\"]/";
        $this->expectException(SolutionExecutionException::class);
        $this->expectExceptionMessageMatches($regex);
        $this->runner->verify(new Input('app', ['program' => '']));
    }

    public function testVerifyReturnsSuccessIfSolutionOutputMatchesUserOutput(): void
    {
        $solution = SingleFileSolution::fromFile(realpath(__DIR__ . '/../res/cli/solution.php'));
        $this->exercise->setSolution($solution);
        $this->exercise->setScenario((new CliScenario())->withExecution([1, 2, 3]));

        $this->assertInstanceOf(
            CliResult::class,
            $res = $this->runner->verify(new Input('app', ['program' => __DIR__ . '/../res/cli/user.php']))
        );

        $this->assertTrue($res->isSuccessful());
    }

    public function testSuccessWithSingleSetOfArgsForBC(): void
    {
        $solution = SingleFileSolution::fromFile(realpath(__DIR__ . '/../res/cli/solution.php'));
        $this->exercise->setSolution($solution);
        $this->exercise->setScenario((new CliScenario())->withExecution([1, 2, 3]));

        $this->assertInstanceOf(
            CliResult::class,
            $res = $this->runner->verify(new Input('app', ['program' => __DIR__ . '/../res/cli/user.php']))
        );

        $this->assertTrue($res->isSuccessful());
    }

    public function testVerifyReturnsFailureIfUserSolutionFailsToExecute(): void
    {
        $solution = SingleFileSolution::fromFile(realpath(__DIR__ . '/../res/cli/solution.php'));
        $this->exercise->setSolution($solution);
        $this->exercise->setScenario((new CliScenario())->withExecution());

        $failure = $this->runner->verify(new Input('app', ['program' => __DIR__ . '/../res/cli/user-error.php']));

        $failureMsg  = '/^PHP Code failed to execute. Error: "PHP Parse error:  syntax error, ';
        $failureMsg .= "unexpected end of file, expecting ['\"][,;]['\"] or ['\"][;,]['\"]/";

        $this->assertInstanceOf(CliResult::class, $failure);
        $this->assertCount(1, $failure);

        $result = iterator_to_array($failure)[0];
        $this->assertInstanceOf(GenericFailure::class, $result);
        $this->assertMatchesRegularExpression($failureMsg, $result->getReason());
    }

    public function testVerifyReturnsFailureIfSolutionOutputDoesNotMatchUserOutput(): void
    {
        $solution = SingleFileSolution::fromFile(realpath(__DIR__ . '/../res/cli/solution.php'));
        $this->exercise->setSolution($solution);
        $this->exercise->setScenario((new CliScenario())->withExecution([1, 2, 3]));

        $failure = $this->runner->verify(new Input('app', ['program' => __DIR__ . '/../res/cli/user-wrong.php']));

        $this->assertInstanceOf(CliResult::class, $failure);
        $this->assertCount(1, $failure);

        $result = iterator_to_array($failure)[0];
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

        $this->exercise->setScenario(
            (new CliScenario())
                ->withExecution([1, 2, 3])
                ->withExecution([4, 5, 6])
        );


        $result = $this->runner->run(new Input('app', ['program' => __DIR__ . '/../res/cli/user.php']), $output);

        $this->assertTrue($result);
    }

    public function testRunPassesOutputAndReturnsFailureIfScriptFails(): void
    {
        $output = new StdOutput(new Color(), $this->createMock(Terminal::class));
        $this->exercise->setScenario((new CliScenario())->withExecution([1, 2, 3]));

        $this->expectOutputRegex(
            "/(PHP )?Parse error:\W+syntax error, unexpected end of file, expecting ['\"][,;]['\"] or ['\"][;,]['\"] /"
        );

        $success = $this->runner->run(new Input('app', ['program' => __DIR__ . '/../res/cli/user-error.php']), $output);
        $this->assertFalse($success);
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
        $this->exercise->setSolution($solution);
        $this->exercise->setScenario((new CliScenario())->withExecution([1, 2, 3]));

        $this->assertInstanceOf(
            CliResult::class,
            $res = $this->runner->verify(new Input('app', ['program' => __DIR__ . '/../res/cli/user.php']))
        );

        $this->assertTrue($res->isSuccessful());
        $this->assertEquals([1, 2, 3, 4], $res->getResults()[0]->getArgs()->getArrayCopy());
    }
}
