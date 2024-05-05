<?php

namespace PhpSchool\PhpWorkshopTest\Process;

use PhpSchool\PhpWorkshop\Process\ProcessInput;
use PhpSchool\PhpWorkshop\Process\ProcessNotFoundException;
use Symfony\Component\Process\ExecutableFinder;
use PhpSchool\PhpWorkshop\Process\HostProcessFactory;
use PHPUnit\Framework\TestCase;

class HostProcessFactoryTest extends TestCase
{
    public function testCreateThrowsExceptionIfExecutableNotFound(): void
    {
        static::expectException(ProcessNotFoundException::class);

        $finder = $this->createMock(ExecutableFinder::class);
        $finder->expects($this->once())
            ->method('find')
            ->with('composer')
            ->willReturn(null);

        $factory = new HostProcessFactory($finder);
        $input = new ProcessInput('composer', [], __DIR__, []);

        $factory->create($input);
    }

    public function testCreate(): void
    {
        $finder = $this->createMock(ExecutableFinder::class);
        $finder->expects($this->once())
            ->method('find')
            ->with('composer')
            ->willReturn('/usr/local/bin/composer');

        $factory = new HostProcessFactory($finder);
        $input = new ProcessInput('composer', [], __DIR__, []);

        $process = $factory->create($input);
        static::assertSame("'/usr/local/bin/composer'", $process->getCommandLine());
    }

    public function testCreateWithArgs(): void
    {
        $finder = $this->createMock(ExecutableFinder::class);
        $finder->expects($this->once())
            ->method('find')
            ->with('composer')
            ->willReturn('/usr/local/bin/composer');

        $factory = new HostProcessFactory($finder);
        $input = new ProcessInput('composer', ['one', 'two'], __DIR__, []);

        $process = $factory->create($input);
        static::assertSame("'/usr/local/bin/composer' 'one' 'two'", $process->getCommandLine());
    }

    public function testCreateWithEnv(): void
    {
        $finder = $this->createMock(ExecutableFinder::class);
        $finder->expects($this->once())
            ->method('find')
            ->with('composer')
            ->willReturn('/usr/local/bin/composer');

        $factory = new HostProcessFactory($finder);
        $input = new ProcessInput('composer', ['one', 'two'], __DIR__, ['SOME_VAR' => 'value']);

        $process = $factory->create($input);
        static::assertSame(['SOME_VAR' => 'value'], $process->getEnv());
    }

    public function testWithInput(): void
    {
        $finder = $this->createMock(ExecutableFinder::class);
        $finder->expects($this->once())
            ->method('find')
            ->with('composer')
            ->willReturn('/usr/local/bin/composer');

        $factory = new HostProcessFactory($finder);
        $input = new ProcessInput('composer', [], __DIR__, [], 'someinput');

        $process = $factory->create($input);
        static::assertSame('someinput', $process->getInput());
    }
}
