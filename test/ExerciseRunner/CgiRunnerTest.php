<?php

namespace PhpSchool\PhpWorkshopTest\ExerciseRunner;

use Colors\Color;
use GuzzleHttp\Psr7\Request;
use PhpSchool\PhpWorkshop\Check\CodeExistsCheck;
use PhpSchool\PhpWorkshop\Listener\OutputRunInfoListener;
use PhpSchool\Terminal\Terminal;
use PhpSchool\PhpWorkshop\Check\CodeParseCheck;
use PhpSchool\PhpWorkshop\Check\FileExistsCheck;
use PhpSchool\PhpWorkshop\Check\PhpLintCheck;
use PhpSchool\PhpWorkshop\Event\EventDispatcher;
use PhpSchool\PhpWorkshop\Exception\SolutionExecutionException;
use PhpSchool\PhpWorkshop\Exercise\ExerciseType;
use PhpSchool\PhpWorkshop\ExerciseRunner\CgiRunner;
use PhpSchool\PhpWorkshop\Input\Input;
use PhpSchool\PhpWorkshop\Output\StdOutput;
use PhpSchool\PhpWorkshop\Result\Cgi\RequestFailure;
use PhpSchool\PhpWorkshop\Result\Cgi\CgiResult;
use PhpSchool\PhpWorkshop\Result\Failure;
use PhpSchool\PhpWorkshop\ResultAggregator;
use PhpSchool\PhpWorkshop\Solution\SingleFileSolution;
use PhpSchool\PhpWorkshop\Utils\RequestRenderer;
use PhpSchool\PhpWorkshopTest\Asset\CgiExerciseInterface;
use PHPUnit\Framework\TestCase;
use Yoast\PHPUnitPolyfills\Polyfills\AssertionRenames;

class CgiRunnerTest extends TestCase
{
    use AssertionRenames;

    /** @var  CgiRunner */
    private $runner;

    /**
     * @var CgiExerciseInterface
     */
    private $exercise;

    /**
     * @var EventDispatcher
     */
    private $eventDispatcher;

    public function setUp(): void
    {
        $this->exercise = $this->createMock(CgiExerciseInterface::class);
        $this->eventDispatcher = new EventDispatcher(new ResultAggregator());
        $this->runner = new CgiRunner($this->exercise, $this->eventDispatcher);

        $this->exercise
            ->method('getType')
            ->willReturn(ExerciseType::CGI());

        $this->assertEquals('CGI Program Runner', $this->runner->getName());
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
        $solution = SingleFileSolution::fromFile(__DIR__ . '/../res/cgi/solution-error.php');
        $this->exercise
            ->expects($this->once())
            ->method('getSolution')
            ->willReturn($solution);

        $request = (new Request('GET', 'http://some.site?number=5'));

        $this->exercise
            ->expects($this->once())
            ->method('getRequests')
            ->willReturn([$request]);

        $regex  = "/^PHP Code failed to execute\. Error: \"PHP Parse error:  syntax error, unexpected end of file in/";
        $this->expectException(SolutionExecutionException::class);
        $this->expectExceptionMessageMatches($regex);
        $this->runner->verify(new Input('app', ['program' => '']));
    }

    public function testVerifyReturnsSuccessIfGetSolutionOutputMatchesUserOutput(): void
    {
        $solution = SingleFileSolution::fromFile(__DIR__ . '/../res/cgi/get-solution.php');
        $this->exercise
            ->expects($this->once())
            ->method('getSolution')
            ->willReturn($solution);

        $request = (new Request('GET', 'http://some.site?number=5'));

        $this->exercise
            ->expects($this->once())
            ->method('getRequests')
            ->willReturn([$request]);

        $this->assertInstanceOf(
            CgiResult::class,
            $this->runner->verify(new Input('app', ['program' => realpath(__DIR__ . '/../res/cgi/get-solution.php')]))
        );
    }

    public function testVerifyReturnsSuccessIfPostSolutionOutputMatchesUserOutput(): void
    {
        $solution = SingleFileSolution::fromFile(__DIR__ . '/../res/cgi/post-solution.php');
        $this->exercise
            ->expects($this->once())
            ->method('getSolution')
            ->willReturn($solution);

        $request = (new Request('POST', 'http://some.site'))
            ->withHeader('Content-Type', 'application/x-www-form-urlencoded');

        $request->getBody()->write('number=5');

        $this->exercise
            ->expects($this->once())
            ->method('getRequests')
            ->willReturn([$request]);

        $this->assertInstanceOf(
            CgiResult::class,
            $res = $this->runner->verify(
                new Input('app', ['program' => realpath(__DIR__ . '/../res/cgi/post-solution.php')])
            )
        );

        $this->assertTrue($res->isSuccessful());
    }

    public function testVerifyReturnsSuccessIfPostSolutionOutputMatchesUserOutputWithMultipleParams(): void
    {
        $solution = SingleFileSolution::fromFile(__DIR__ . '/../res/cgi/post-multiple-solution.php');
        $this->exercise
            ->expects($this->once())
            ->method('getSolution')
            ->willReturn($solution);

        $request = (new Request('POST', 'http://some.site'))
            ->withHeader('Content-Type', 'application/x-www-form-urlencoded');

        $request->getBody()->write('number=5&start=4');

        $this->exercise
            ->expects($this->once())
            ->method('getRequests')
            ->willReturn([$request]);

        $result = $this->runner->verify(
            new Input('app', ['program' => realpath(__DIR__ . '/../res/cgi/post-multiple-solution.php')])
        );

        $this->assertInstanceOf(CgiResult::class, $result);
    }

    public function testVerifyReturnsFailureIfUserSolutionFailsToExecute(): void
    {
        $solution = SingleFileSolution::fromFile(__DIR__ . '/../res/cgi/get-solution.php');
        $this->exercise
            ->expects($this->once())
            ->method('getSolution')
            ->willReturn($solution);

        $request = (new Request('GET', 'http://some.site?number=5'));

        $this->exercise
            ->expects($this->once())
            ->method('getRequests')
            ->willReturn([$request]);

        $failure = $this->runner->verify(
            new Input('app', ['program' => realpath(__DIR__ . '/../res/cgi/user-error.php')])
        );

        $this->assertInstanceOf(CgiResult::class, $failure);
        $this->assertCount(1, $failure);

        $result = iterator_to_array($failure)[0];
        $this->assertInstanceOf(Failure::class, $result);

        $failureMsg  = '/^PHP Code failed to execute. Error: "PHP Parse error:  syntax error, unexpected end of file';
        $failureMsg .= ' in/';
        $this->assertMatchesRegularExpression($failureMsg, $result->getReason());
    }

    public function testVerifyReturnsFailureIfSolutionOutputDoesNotMatchUserOutput(): void
    {
        $solution = SingleFileSolution::fromFile(__DIR__ . '/../res/cgi/get-solution.php');
        $this->exercise
            ->expects($this->once())
            ->method('getSolution')
            ->willReturn($solution);

        $request = (new Request('GET', 'http://some.site?number=5'));

        $this->exercise
            ->expects($this->once())
            ->method('getRequests')
            ->willReturn([$request]);

        $failure = $this->runner->verify(
            new Input('app', ['program' => realpath(__DIR__ . '/../res/cgi/get-user-wrong.php')])
        );

        $this->assertInstanceOf(CgiResult::class, $failure);
        $this->assertCount(1, $failure);

        $result = iterator_to_array($failure)[0];
        $this->assertInstanceOf(RequestFailure::class, $result);
        $this->assertEquals('10', $result->getExpectedOutput());
        $this->assertEquals('15', $result->getActualOutput());
        $this->assertEquals(['Content-type' => 'text/html; charset=UTF-8'], $result->getExpectedHeaders());
        $this->assertEquals(['Content-type' => 'text/html; charset=UTF-8'], $result->getActualHeaders());
    }

    public function testVerifyReturnsFailureIfSolutionOutputHeadersDoesNotMatchUserOutputHeaders(): void
    {
        $solution = SingleFileSolution::fromFile(__DIR__ . '/../res/cgi/get-solution-header.php');
        $this->exercise
            ->expects($this->once())
            ->method('getSolution')
            ->willReturn($solution);

        $request = (new Request('GET', 'http://some.site?number=5'));

        $this->exercise
            ->expects($this->once())
            ->method('getRequests')
            ->willReturn([$request]);

        $failure = $this->runner->verify(
            new Input('app', ['program' => realpath(__DIR__ . '/../res/cgi/get-user-header-wrong.php')])
        );

        $this->assertInstanceOf(CgiResult::class, $failure);
        $this->assertCount(1, $failure);

        $result = iterator_to_array($failure)[0];
        $this->assertInstanceOf(RequestFailure::class, $result);

        $this->assertSame($result->getExpectedOutput(), $result->getActualOutput());
        $this->assertEquals(
            [
                'Pragma'        => 'cache',
                'Content-type'  => 'text/html; charset=UTF-8'
            ],
            $result->getExpectedHeaders()
        );
        $this->assertEquals(
            [
                'Pragma'        => 'no-cache',
                'Content-type'  => 'text/html; charset=UTF-8'
            ],
            $result->getActualHeaders()
        );
    }

    public function testRunPassesOutputAndReturnsSuccessIfAllRequestsAreSuccessful(): void
    {
        $color = new Color();
        $color->setForceStyle(true);
        $output = new StdOutput($color, $this->createMock(Terminal::class));
        $request1 = (new Request('GET', 'http://some.site?number=5'));
        $request2 = (new Request('GET', 'http://some.site?number=6'));

        $this->eventDispatcher->listen(
            'cgi.run.student-execute.pre',
            new OutputRunInfoListener($output, new RequestRenderer())
        );

        $this->exercise
            ->expects($this->once())
            ->method('getRequests')
            ->willReturn([$request1, $request2]);

        $exp  = "\n\e[1m\e[4mRequest";
        $exp .= "\e[0m\e[0m\n\n";
        $exp .= "URL:     http://some.site?number=5\n";
        $exp .= "METHOD:  GET\n";
        $exp .= "HEADERS: Host: some.site\n\n";
        $exp .= "\e[1m\e[4mOutput";
        $exp .= "\e[0m\e[0m\n\n";
        $exp .= "Content-type: text/html; charset=UTF-8\r\n\r\n";
        $exp .= "10\n";
        $exp .= "\e[33m\e[0m\n";
        $exp .= "\e[1m\e[4mRequest";
        $exp .= "\e[0m\e[0m\n\n";
        $exp .= "URL:     http://some.site?number=6\n";
        $exp .= "METHOD:  GET\n";
        $exp .= "HEADERS: Host: some.site\n\n";
        $exp .= "\e[1m\e[4mOutput";
        $exp .= "\e[0m\e[0m\n\n";
        $exp .= "Content-type: text/html; charset=UTF-8\r\n\r\n";
        $exp .= "12\n";
        $exp .= "\e[33m\e[0m";

        $this->expectOutputString($exp);

        $success = $this->runner->run(
            new Input('app', ['program' => realpath(__DIR__ . '/../res/cgi/get-solution.php')]),
            $output
        );

        $this->assertTrue($success);
    }

    public function testRunPassesOutputAndReturnsFailureIfARequestFails(): void
    {
        $color = new Color();
        $color->setForceStyle(true);
        $output = new StdOutput($color, $this->createMock(Terminal::class));
        $request1 = (new Request('GET', 'http://some.site?number=5'));

        $this->eventDispatcher->listen(
            'cgi.run.student-execute.pre',
            new OutputRunInfoListener($output, new RequestRenderer())
        );

        $this->exercise
            ->expects($this->once())
            ->method('getRequests')
            ->willReturn([$request1]);

        $exp = "\n\e[1m\e[4mRequest";
        $exp .= "\e[0m\e[0m\n\n";
        $exp .= "URL:     http://some.site?number=5\n";
        $exp .= "METHOD:  GET\n";
        $exp .= "HEADERS: Host: some.site\n\n";
        $exp .= "\e[1m\e[4mOutput";
        $exp .= "\e[0m\e[0m\n\n";
        $exp .= "Status: 404 Not Found\r\n";
        $exp .= "Content-type: text/html; charset=UTF-8\r\n\r\n";
        $exp .= "No input file specified.\n\n";
        $exp .= "\e[33m\e[0m";

        $this->expectOutputString($exp);

        $success = $this->runner->run(
            new Input('app', ['program' => 'not-existing-file.php']),
            $output
        );
        $this->assertFalse($success);
    }
}
