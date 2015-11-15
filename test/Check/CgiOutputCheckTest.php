<?php

namespace PhpSchool\PhpWorkshopTest\Check;

use InvalidArgumentException;
use PhpSchool\PhpWorkshop\Check\CgiOutputCheck;
use PhpSchool\PhpWorkshop\Result\CgiOutBodyFailure;
use PhpSchool\PhpWorkshop\Result\CgiOutFailure;
use PhpSchool\PhpWorkshop\Result\CgiOutHeadersFailure;
use PhpSchool\PhpWorkshop\Result\CgiOutResult;
use PhpSchool\PhpWorkshop\StringBody;
use PhpSchool\PhpWorkshopTest\Asset\CgiOutExercise;
use PHPUnit_Framework_TestCase;
use PhpSchool\PhpWorkshop\Exception\SolutionExecutionException;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\Result\Failure;
use PhpSchool\PhpWorkshop\Result\Success;
use Zend\Diactoros\Request;
use Zend\Diactoros\Uri;

/**
 * Class CgiOutputCheckTest
 * @package PhpSchool\PhpWorkshopTest
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class CgiOutputCheckTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var StdOutCheck
     */
    private $check;

    /**
     * @var ExerciseInterface
     */
    private $exercise;

    public function setUp()
    {
        $this->check = new CgiOutputCheck;
        $this->assertFalse($this->check->breakChainOnFailure());

        $this->exercise = $this->getMock(CgiOutExercise::class);
        $this->assertEquals('CGI Program Output Check', $this->check->getName());
    }

    public function testExceptionIsThrownIfNotValidExercise()
    {
        $exercise = $this->getMock(ExerciseInterface::class);
        $this->setExpectedException(InvalidArgumentException::class);

        $this->check->check($exercise, '');
    }
    
    public function testCheckThrowsExceptionIfSolutionFailsExecution()
    {
        $this->exercise
            ->expects($this->once())
            ->method('getSolution')
            ->will($this->returnValue(realpath(__DIR__ . '/../res/cgi-out/solution-error.php')));

        $request = (new Request)
            ->withMethod('GET')
            ->withUri(new Uri('http://some.site?number=5'));

        $this->exercise
            ->expects($this->once())
            ->method('getRequests')
            ->will($this->returnValue([$request]));

        $regex  = "/^PHP Code failed to execute\\. Error: \"PHP Parse error:  syntax error, unexpected end of file in/";
        $this->setExpectedExceptionRegExp(SolutionExecutionException::class, $regex);
        $this->check->check($this->exercise, '');
    }

    public function testSuccessIsReturnedIfGetSolutionOutputMatchesUserOutput()
    {
        $this->exercise
            ->expects($this->once())
            ->method('getSolution')
            ->will($this->returnValue(realpath(__DIR__ . '/../res/cgi-out/get-solution.php')));

        $request = (new Request)
            ->withMethod('GET')
            ->withUri(new Uri('http://some.site?number=5'));
        
        $this->exercise
            ->expects($this->once())
            ->method('getRequests')
            ->will($this->returnValue([$request]));

        $this->assertInstanceOf(
            CgiOutResult::class,
            $this->check->check($this->exercise, realpath(__DIR__ . '/../res/cgi-out/get-solution.php'))
        );
    }

    public function testSuccessIsReturnedIfPostSolutionOutputMatchesUserOutput()
    {
        $this->exercise
            ->expects($this->once())
            ->method('getSolution')
            ->will($this->returnValue(realpath(__DIR__ . '/../res/cgi-out/post-solution.php')));
        
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
            $this->check->check($this->exercise, realpath(__DIR__ . '/../res/cgi-out/post-solution.php'))
        );
    }
    public function testFailureIsReturnedIfUserSolutionFailsToExecute()
    {
        $this->exercise
            ->expects($this->once())
            ->method('getSolution')
            ->will($this->returnValue(realpath(__DIR__ . '/../res/cgi-out/get-solution.php')));

        $request = (new Request)
            ->withMethod('GET')
            ->withUri(new Uri('http://some.site?number=5'));

        $this->exercise
            ->expects($this->once())
            ->method('getRequests')
            ->will($this->returnValue([$request]));

        $failure = $this->check->check($this->exercise, realpath(__DIR__ . '/../res/cgi-out/user-error.php'));

        $failureMsg  = "/^PHP Code failed to execute. Error: \"PHP Parse error:  syntax error, unexpected end of file";
        $failureMsg .= " in/";
        
        $this->assertInstanceOf(CgiOutResult::class, $failure);
        //$this->assertRegExp($failureMsg, $failure->getReason());
    }

    public function testFailureIsReturnedIfSolutionOutputDoesNotMatchUserOutput()
    {
        $this->exercise
            ->expects($this->once())
            ->method('getSolution')
            ->will($this->returnValue(realpath(__DIR__ . '/../res/cgi-out/get-solution.php')));

        $request = (new Request)
            ->withMethod('GET')
            ->withUri(new Uri('http://some.site?number=5'));

        $this->exercise
            ->expects($this->once())
            ->method('getRequests')
            ->will($this->returnValue([$request]));
        
        $failure = $this->check->check($this->exercise, realpath(__DIR__ . '/../res/cgi-out/get-user-wrong.php'));

        $this->assertInstanceOf(CgiOutResult::class, $failure);
        //$this->assertEquals('10', $failure->getExpectedOutput());
        //$this->assertEquals('15', $failure->getActualOutput());
    }

    public function testFailureIsReturnedIfSolutionOutputHeadersDoesNotMatchUserOutputHeaders()
    {
        $this->exercise
            ->expects($this->once())
            ->method('getSolution')
            ->will($this->returnValue(realpath(__DIR__ . '/../res/cgi-out/get-solution-header.php')));

        $request = (new Request)
            ->withMethod('GET')
            ->withUri(new Uri('http://some.site?number=5'));

        $this->exercise
            ->expects($this->once())
            ->method('getRequests')
            ->will($this->returnValue([$request]));

        $failure = $this->check->check(
            $this->exercise,
            realpath(__DIR__ . '/../res/cgi-out/get-user-header-wrong.php')
        );

        $this->assertInstanceOf(CgiOutResult::class, $failure);
//        $this->assertEquals(
//            [
//                'Pragma'        => 'cache',
//                'Content-type'  => 'text/html; charset=UTF-8'
//            ],
//            $failure->getExpectedHeaders()
//        );
//        $this->assertEquals(
//            [
//                'Pragma'        => 'no-cache',
//                'Content-type'  => 'text/html; charset=UTF-8'
//            ],
//            $failure->getActualHeaders()
//        );
    }
}
