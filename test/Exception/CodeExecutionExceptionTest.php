<?php

namespace PhpSchool\PhpWorkshopTest\Exception;

use PhpSchool\PhpWorkshop\Exception\CodeExecutionException;
use PHPUnit\Framework\TestCase;
use PhpSchool\PhpWorkshop\Process\Sandbox\SandboxExecution;
use Symfony\Component\Process\Process;

class CodeExecutionExceptionTest extends TestCase
{
    public function testException(): void
    {
        $e = new CodeExecutionException('nope');
        $this->assertEquals('nope', $e->getMessage());
    }

    public function testFromProcessUsesErrorOutputIfNotEmpty(): void
    {
        $execution = new SandboxExecution(Process::fromShellCommandline('echo "Std Output"; echo "Error Output" >&2'));
        $execution->mustRun();

        $e = CodeExecutionException::fromExecution($execution);
        $this->assertEquals('PHP Code failed to execute. Error: "Error Output"', $e->getMessage());
    }

    public function testFromProcessUsesStdOutputIfErrorOutputEmpty(): void
    {
        $execution = new SandboxExecution(Process::fromShellCommandline('echo "Std Output"'));
        $execution->mustRun();

        $e = CodeExecutionException::fromExecution($execution);
        $this->assertEquals('PHP Code failed to execute. Error: "Std Output"', $e->getMessage());
    }
}
