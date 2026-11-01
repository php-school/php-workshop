<?php

namespace PhpSchool\PhpWorkshopTest\Process;

use PhpSchool\PhpWorkshop\Process\ProcessInput;
use PhpSchool\PhpWorkshop\Process\Workspace;
use PhpSchool\PhpWorkshop\Process\ProcessNotFoundException;
use Symfony\Component\Process\ExecutableFinder;
use PhpSchool\PhpWorkshop\Process\HostSandbox;
use PHPUnit\Framework\TestCase;

class HostSandboxTest extends TestCase
{
    public function testCreateThrowsExceptionIfExecutableNotFound(): void
    {
        static::expectException(ProcessNotFoundException::class);

        $finder = $this->createMock(ExecutableFinder::class);
        $finder->expects($this->once())
            ->method('find')
            ->with('composer')
            ->willReturn(null);

        $factory = new HostSandbox($finder);
        $input = new ProcessInput('composer', [], []);

        $factory->create(new Workspace(__DIR__), $input);
    }

    public function testCreate(): void
    {
        $finder = $this->createMock(ExecutableFinder::class);
        $finder->expects($this->once())
            ->method('find')
            ->with('composer')
            ->willReturn('/usr/local/bin/composer');

        $factory = new HostSandbox($finder);
        $input = new ProcessInput('composer', [], []);

        $process = $factory->create(new Workspace(__DIR__), $input)->process;
        static::assertSame("'/usr/local/bin/composer'", $process->getCommandLine());
    }

    public function testPickedPortIsFreeOnThisMachine(): void
    {
        $server = stream_socket_server('tcp://127.0.0.1:' . (new HostSandbox())->prepare(__DIR__, true)->port);
        static::assertNotFalse($server);
        fclose($server);
    }

    public function testAddressIsThePortOnThisMachine(): void
    {
        $execution = (new HostSandbox())->create(new Workspace(__DIR__, 54321), new ProcessInput('php', [], []));

        static::assertSame('127.0.0.1:54321', $execution->getAddress());
    }

    public function testCreateWithArgs(): void
    {
        $finder = $this->createMock(ExecutableFinder::class);
        $finder->expects($this->once())
            ->method('find')
            ->with('composer')
            ->willReturn('/usr/local/bin/composer');

        $factory = new HostSandbox($finder);
        $input = new ProcessInput('composer', ['one', 'two'], []);

        $process = $factory->create(new Workspace(__DIR__), $input)->process;
        static::assertSame("'/usr/local/bin/composer' 'one' 'two'", $process->getCommandLine());
    }

    public function testCreateWithEnv(): void
    {
        $finder = $this->createMock(ExecutableFinder::class);
        $finder->expects($this->once())
            ->method('find')
            ->with('composer')
            ->willReturn('/usr/local/bin/composer');

        $factory = new HostSandbox($finder);
        $input = new ProcessInput('composer', ['one', 'two'], ['SOME_VAR' => 'value']);

        $process = $factory->create(new Workspace(__DIR__), $input)->process;
        static::assertSame('value', $process->getEnv()['SOME_VAR']);
    }

    public function testInheritedEnvIsRemovedExceptAllowed(): void
    {
        putenv('PHPSCHOOL_SECRET=secret');

        try {
            $process = (new HostSandbox())->create(
                new Workspace(__DIR__),
                new ProcessInput('php', ['-r', 'echo json_encode([getenv("PHPSCHOOL_SECRET"), getenv("PATH") !== false]);'], []),
            );
            $process->mustRun();

            static::assertSame('[false,true]', $process->getOutput());
        } finally {
            putenv('PHPSCHOOL_SECRET');
        }
    }

    public function testWithInput(): void
    {
        $finder = $this->createMock(ExecutableFinder::class);
        $finder->expects($this->once())
            ->method('find')
            ->with('composer')
            ->willReturn('/usr/local/bin/composer');

        $factory = new HostSandbox($finder);
        $input = new ProcessInput('composer', [], [], 'someinput');

        $process = $factory->create(new Workspace(__DIR__), $input)->process;
        static::assertSame('someinput', $process->getInput());
    }
}
