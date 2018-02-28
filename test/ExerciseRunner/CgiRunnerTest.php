<?php

namespace PhpSchool\PhpWorkshopTest\ExerciseRunner;

use Colors\Color;
use PhpSchool\CliMenu\Terminal\TerminalInterface;
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
use PHPUnit_Framework_TestCase;
use Zend\Diactoros\Request;
use Zend\Diactoros\Uri;

/**
 * @package PhpSchool\PhpWorkshop\ExerciseRunner
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class CgiRunnerTest extends PHPUnit_Framework_TestCase
{
    /** @var  CgiRunner */
    private $runner;

    /**
     * @var CgiExerciseInterface
     */
    private $exercise;

    public function setUp()
    {
        $this->exercise = $this->createMock(CgiExerciseInterface::class);
        $this->runner = new CgiRunner($this->exercise, new EventDispatcher(new ResultAggregator), new RequestRenderer);

        $this->exercise
            ->expects($this->any())
            ->method('getType')
            ->will($this->returnValue(ExerciseType::CGI()));

        $this->assertEquals('CGI Program Runner', $this->runner->getName());
    }

    public function testRequiredChecks()
    {
        $requiredChecks = [
            FileExistsCheck::class,
            PhpLintCheck::class,
            CodeParseCheck::class,
        ];

        $this->assertEquals($requiredChecks, $this->runner->getRequiredChecks());
    }

    public function testVerifyThrowsExceptionIfSolutionFailsExecution()
    {
        $solution = SingleFileSolution::fromFile(__DIR__ . '/../res/cgi/solution-error.php');
        $this->exercise
            ->expects($this->once())
            ->method('getSolution')
            ->will($this->returnValue($solution));

        $request = (new Request)
            ->withMethod('GET')
            ->withUri(new Uri('http://some.site?number=5'));

        $this->exercise
            ->expects($this->once())
            ->method('getRequests')
            ->will($this->returnValue([$request]));

        $regex  = "/^PHP Code failed to execute\\. Error: \"PHP Parse error:  syntax error, unexpected end of file in/";
        $this->expectException(SolutionExecutionException::class);
        $this->expectExceptionMessageRegExp($regex);
        $this->runner->verify(new Input('app', ['program' => '']));
    }

    public function testVerifyReturnsSuccessIfGetSolutionOutputMatchesUserOutput()
    {
        $solution = SingleFileSolution::fromFile(__DIR__ . '/../res/cgi/get-solution.php');
        $this->exercise
            ->expects($this->once())
            ->method('getSolution')
            ->will($this->returnValue($solution));

        $request = (new Request)
            ->withMethod('GET')
            ->withUri(new Uri('http://some.site?number=5'));

        $this->exercise
            ->expects($this->once())
            ->method('getRequests')
            ->will($this->returnValue([$request]));

        $this->assertInstanceOf(
            CgiResult::class,
            $this->runner->verify(new Input('app', ['program' => realpath(__DIR__ . '/../res/cgi/get-solution.php')]))
        );
    }

    public function testVerifyReturnsSuccessIfPostSolutionOutputMatchesUserOutput()
    {
        $solution = SingleFileSolution::fromFile(__DIR__ . '/../res/cgi/post-solution.php');
        $this->exercise
            ->expects($this->once())
            ->method('getSolution')
            ->will($this->returnValue($solution));

        $request = (new Request)
            ->withMethod('POST')
            ->withUri(new Uri('http://some.site'))
            ->withHeader('Content-Type', 'application/x-www-form-urlencoded');

        $request->getBody()->write('number=5');

        $this->exercise
            ->expects($this->once())
            ->method('getRequests')
            ->will($this->returnValue([$request]));

        $this->assertInstanceOf(
            CgiResult::class,
            $res = $this->runner->verify(
                new Input('app', ['program' => realpath(__DIR__ . '/../res/cgi/post-solution.php')])
            )
        );

        $this->assertTrue($res->isSuccessful());
    }

    public function testVerifyReturnsSuccessIfPostSolutionOutputMatchesUserOutputWithMultipleParams()
    {
        $solution = SingleFileSolution::fromFile(__DIR__ . '/../res/cgi/post-multiple-solution.php');
        $this->exercise
            ->expects($this->once())
            ->method('getSolution')
            ->will($this->returnValue($solution));

        $request = (new Request)
            ->withMethod('POST')
            ->withUri(new Uri('http://some.site'))
            ->withHeader('Content-Type', 'application/x-www-form-urlencoded');

        $request->getBody()->write('number=5&start=4');

        $this->exercise
            ->expects($this->once())
            ->method('getRequests')
            ->will($this->returnValue([$request]));

        $result = $this->runner->verify(
            new Input('app', ['program' => realpath(__DIR__ . '/../res/cgi/post-multiple-solution.php')])
        );

        $this->assertInstanceOf(CgiResult::class, $result);
    }

    public function testVerifyReturnsFailureIfUserSolutionFailsToExecute()
    {
        $solution = SingleFileSolution::fromFile(__DIR__ . '/../res/cgi/get-solution.php');
        $this->exercise
            ->expects($this->once())
            ->method('getSolution')
            ->will($this->returnValue($solution));

        $request = (new Request)
            ->withMethod('GET')
            ->withUri(new Uri('http://some.site?number=5'));

        $this->exercise
            ->expects($this->once())
            ->method('getRequests')
            ->will($this->returnValue([$request]));

        $failure = $this->runner->verify(
            new Input('app', ['program' => realpath(__DIR__ . '/../res/cgi/user-error.php')])
        );

        $this->assertInstanceOf(CgiResult::class, $failure);
        $this->assertCount(1, $failure);

        $result = iterator_to_array($failure)[0];
        $this->assertInstanceOf(Failure::class, $result);

        $failureMsg  = "/^PHP Code failed to execute. Error: \"PHP Parse error:  syntax error, unexpected end of file";
        $failureMsg .= " in/";
        $this->assertRegExp($failureMsg, $result->getReason());
    }

    public function testVerifyReturnsFailureIfSolutionOutputDoesNotMatchUserOutput()
    {
        $solution = SingleFileSolution::fromFile(__DIR__ . '/../res/cgi/get-solution.php');
        $this->exercise
            ->expects($this->once())
            ->method('getSolution')
            ->will($this->returnValue($solution));

        $request = (new Request)
            ->withMethod('GET')
            ->withUri(new Uri('http://some.site?number=5'));

        $this->exercise
            ->expects($this->once())
            ->method('getRequests')
            ->will($this->returnValue([$request]));

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

    public function testVerifyReturnsFailureIfSolutionOutputHeadersDoesNotMatchUserOutputHeaders()
    {
        $solution = SingleFileSolution::fromFile(__DIR__ . '/../res/cgi/get-solution-header.php');
        $this->exercise
            ->expects($this->once())
            ->method('getSolution')
            ->will($this->returnValue($solution));

        $request = (new Request)
            ->withMethod('GET')
            ->withUri(new Uri('http://some.site?number=5'));

        $this->exercise
            ->expects($this->once())
            ->method('getRequests')
            ->will($this->returnValue([$request]));

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

    public function testRunPassesOutputAndReturnsSuccessIfAllRequestsAreSuccessful()
    {
        $color = new Color;
        $color->setForceStyle(true);
        $output = new StdOutput($color, $this->createMock(TerminalInterface::class));
        $request1 = (new Request)
            ->withMethod('GET')
            ->withUri(new Uri('http://some.site?number=5'));

        $request2 = (new Request)
            ->withMethod('GET')
            ->withUri(new Uri('http://some.site?number=6'));

        $this->exercise
            ->expects($this->once())
            ->method('getRequests')
            ->will($this->returnValue([$request1, $request2]));

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

    public function testRunPassesOutputAndReturnsFailureIfARequestFails()
    {
        $color = new Color;
        $color->setForceStyle(true);
        $output = new StdOutput($color, $this->createMock(TerminalInterface::class));
        $request1 = (new Request)
            ->withMethod('GET')
            ->withUri(new Uri('http://some.site?number=5'));

        $this->exercise
            ->expects($this->once())
            ->method('getRequests')
            ->will($this->returnValue([$request1]));

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
