<?php

namespace PhpSchool\PhpWorkshopTest\Exception;

use PhpSchool\PhpWorkshop\Exception\CodeExecutionException;
use PHPUnit_Framework_TestCase;
use Symfony\Component\Process\Process;

/**
 * Class CodeExecutionExceptionTest
 * @package PhpSchool\PhpWorkshopTest\Exception
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class CodeExecutionExceptionTest extends PHPUnit_Framework_TestCase
{
    public function testException()
    {
        $e = new CodeExecutionException('nope');
        $this->assertEquals('nope', $e->getMessage());
    }

    public function testFromProcessUsesErrorOutputIfNotEmpty()
    {
        $process = $this->getMockBuilder(Process::class)
            ->disableOriginalConstructor()
            ->getMock();
        
        $process
            ->expects($this->exactly(2))
            ->method('getErrorOutput')
            ->will($this->returnValue('Error Output'));
        
        $e = CodeExecutionException::fromProcess($process);
        $this->assertEquals('PHP Code failed to execute. Error: "Error Output"', $e->getMessage());
    }

    public function testFromProcessUsesStdOutputIfErrorOutputEmpty()
    {
        $process = $this->getMockBuilder(Process::class)
            ->disableOriginalConstructor()
            ->getMock();

        $process
            ->expects($this->exactly(1))
            ->method('getErrorOutput')
            ->will($this->returnValue(''));

        $process
            ->expects($this->exactly(1))
            ->method('getOutput')
            ->will($this->returnValue('Std Output'));

        $e = CodeExecutionException::fromProcess($process);
        $this->assertEquals('PHP Code failed to execute. Error: "Std Output"', $e->getMessage());
    }
}
