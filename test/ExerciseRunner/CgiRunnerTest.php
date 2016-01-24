<?php

namespace PhpSchool\PhpWorkshop\ExerciseRunner;

use Colors\Color;
use InvalidArgumentException;
use PhpSchool\PhpWorkshop\Event\EventDispatcher;
use PhpSchool\PhpWorkshop\Exception\SolutionExecutionException;
use PhpSchool\PhpWorkshop\Exercise\ExerciseType;
use PhpSchool\PhpWorkshop\Output\OutputInterface;
use PhpSchool\PhpWorkshop\Output\StdOutput;
use PhpSchool\PhpWorkshop\Result\CgiOutRequestFailure;
use PhpSchool\PhpWorkshop\Result\CgiOutResult;
use PhpSchool\PhpWorkshop\Result\Failure;
use PhpSchool\PhpWorkshop\ResultAggregator;
use PhpSchool\PhpWorkshop\Solution\SingleFileSolution;
use PhpSchool\PhpWorkshopTest\Asset\CgiExerciseInterface;
use PHPUnit_Framework_TestCase;
use Zend\Diactoros\Request;
use Zend\Diactoros\Uri;

/**
 * Class CgiRunnerTest
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
        $this->exercise = $this->getMock(CgiExerciseInterface::class);
        $this->runner = new CgiRunner($this->exercise, new EventDispatcher(new ResultAggregator));

        $this->exercise
            ->expects($this->any())
            ->method('getType')
            ->will($this->returnValue(ExerciseType::CGI()));

        $this->assertEquals('CGI Program Runner', $this->runner->getName());
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
        $this->setExpectedExceptionRegExp(SolutionExecutionException::class, $regex);
        $this->runner->verify('');
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
            CgiOutResult::class,
            $this->runner->verify(realpath(__DIR__ . '/../res/cgi/get-solution.php'))
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
            CgiOutResult::class,
            $this->runner->verify(realpath(__DIR__ . '/../res/cgi/post-solution.php'))
        );
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

        $failure = $this->runner->verify(realpath(__DIR__ . '/../res/cgi/user-error.php'));
        $this->assertInstanceOf(CgiOutResult::class, $failure);
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

        $failure = $this->runner->verify(realpath(__DIR__ . '/../res/cgi/get-user-wrong.php'));
        $this->assertInstanceOf(CgiOutResult::class, $failure);
        $this->assertCount(1, $failure);

        $result = iterator_to_array($failure)[0];
        $this->assertInstanceOf(CgiOutRequestFailure::class, $result);
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

        $failure = $this->runner->verify(realpath(__DIR__ . '/../res/cgi/get-user-header-wrong.php'));

        $this->assertInstanceOf(CgiOutResult::class, $failure);
        $this->assertCount(1, $failure);

        $result = iterator_to_array($failure)[0];
        $this->assertInstanceOf(CgiOutRequestFailure::class, $result);

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
        $output = new StdOutput(new Color);
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

        $exp = "Content-type: text/html; charset=UTF-8\r\n\r\n10Content-type: text/html; charset=UTF-8\r\n\r\n12";
        $this->expectOutputString($exp);

        $success = $this->runner->run(realpath(__DIR__ . '/../res/cgi/get-solution.php'), $output);
        $this->assertTrue($success);
    }

    public function testRunPassesOutputAndReturnsFailureIfARequestFails()
    {
        $output = new StdOutput(new Color);
        $request1 = (new Request)
            ->withMethod('GET')
            ->withUri(new Uri('http://some.site?number=5'));

        $this->exercise
            ->expects($this->once())
            ->method('getRequests')
            ->will($this->returnValue([$request1]));

        $exp = "Status: 404 Not Found\r\nContent-type: text/html; charset=UTF-8\r\n\r\nNo input file specified.\n";
        $this->expectOutputString($exp);

        $success = $this->runner->run('', $output);
        $this->assertFalse($success);
    }
}
