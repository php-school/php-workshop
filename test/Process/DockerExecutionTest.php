<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshopTest\Process;

use PhpSchool\PhpWorkshop\Process\DockerExecution;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

class DockerExecutionTest extends TestCase
{
    public function testStepsRunAroundTheMainProcess(): void
    {
        $log = tempnam(sys_get_temp_dir(), 'steps');

        $execution = new DockerExecution(
            Process::fromShellCommandline('echo main >> ' . $log),
            [Process::fromShellCommandline('echo before >> ' . $log)],
            [Process::fromShellCommandline('echo after >> ' . $log)],
        );

        $execution->start();
        $execution->wait();

        $this->assertSame("before\nmain\nafter\n", file_get_contents($log));
        unlink($log);
    }

    public function testAfterStepsRunWhenTheMainProcessFails(): void
    {
        $log = tempnam(sys_get_temp_dir(), 'steps');

        $execution = new DockerExecution(
            Process::fromShellCommandline('exit 1'),
            [],
            [Process::fromShellCommandline('echo after >> ' . $log)],
        );

        $execution->start();
        $execution->wait();

        $this->assertFalse($execution->isSuccessful());
        $this->assertSame("after\n", file_get_contents($log));
        unlink($log);
    }
}
