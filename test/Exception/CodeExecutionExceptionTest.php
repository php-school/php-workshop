<?php

namespace PhpSchool\PhpWorkshopTest\Exception;

use PhpSchool\PhpWorkshop\Exception\CodeExecutionException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

/**
 * Class CodeExecutionExceptionTest
 * @package PhpSchool\PhpWorkshopTest\Exception
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class CodeExecutionExceptionTest extends TestCase
{
    public function testException(): void
    {
        $e = new CodeExecutionException('nope');
        $this->assertEquals('nope', $e->getMessage());
    }

    public function testFromProcessUsesErrorOutputIfNotEmpty(): void
    {
        $process = $this->createMock(Process::class);

        $process
            ->expects($this->once())
            ->method('getErrorOutput')
            ->willReturn('Error Output');
        
        $e = CodeExecutionException::fromProcess($process);
        $this->assertEquals('PHP Code failed to execute. Error: "Error Output"', $e->getMessage());
    }

    public function testFromProcessUsesStdOutputIfErrorOutputEmpty(): void
    {
        $process = $this->createMock(Process::class);
        $process
            ->expects($this->once())
            ->method('getErrorOutput')
            ->willReturn('');

        $process
            ->expects($this->once())
            ->method('getOutput')
            ->willReturn('Std Output');

        $e = CodeExecutionException::fromProcess($process);
        $this->assertEquals('PHP Code failed to execute. Error: "Std Output"', $e->getMessage());
    }
}
