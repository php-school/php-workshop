<?php

namespace PhpSchool\PhpWorkshopTest\Process;

use PhpSchool\PhpWorkshop\Process\DockerProcessFactory;
use PhpSchool\PhpWorkshop\Process\ProcessInput;
use PhpSchool\PhpWorkshop\Process\ProcessNotFoundException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\ExecutableFinder;

class DockerProcessFactoryTest extends TestCase
{
    public function testCreateThrowsExceptionIfDockerNotFound(): void
    {
        static::expectException(ProcessNotFoundException::class);

        $finder = $this->createMock(ExecutableFinder::class);
        $finder->expects($this->once())
            ->method('find')
            ->with('docker')
            ->willReturn(null);

        $factory = new DockerProcessFactory('/docker-dir', 'php8appreciate', '/composer/cache/dir', $finder);
        $input = new ProcessInput('composer', [], __DIR__, []);

        $factory->create($input);
    }

    public function testCreate(): void
    {
        $finder = $this->createMock(ExecutableFinder::class);
        $finder->expects($this->once())
            ->method('find')
            ->with('docker')
            ->willReturn('/usr/local/bin/docker');

        $factory = new DockerProcessFactory('/docker-dir', 'php8appreciate', '/composer/cache/dir', $finder);
        $input = new ProcessInput('php', [], __DIR__, []);

        $process = $factory->create($input);
        $cmd  = "'/usr/local/bin/docker' 'compose' '-p' 'php8appreciate' '-f' '.docker/runtime/docker-compose.yml'";
        $cmd .= " 'run' '--rm' '-w' '/solution' 'runtime' 'php'";
        static::assertSame($cmd, $process->getCommandLine());
        static::assertSame('/docker-dir', $process->getWorkingDirectory());
    }

    public function testCreateMountsComposerCacheDirIfExecutableIsComposer(): void
    {
        $finder = $this->createMock(ExecutableFinder::class);
        $finder->expects($this->once())
            ->method('find')
            ->with('docker')
            ->willReturn('/usr/local/bin/docker');

        $factory = new DockerProcessFactory('/docker-dir', 'php8appreciate', '/composer/cache/dir', $finder);
        $input = new ProcessInput('composer', [], __DIR__, []);

        $process = $factory->create($input);
        $cmd  = "'/usr/local/bin/docker' 'compose' '-p' 'php8appreciate' '-f' '.docker/runtime/docker-compose.yml'";
        $cmd .= " 'run' '--rm' '-w' '/solution' '-v' '/composer/cache/dir:/root/.composer/cache' 'runtime' 'composer'";
        static::assertSame($cmd, $process->getCommandLine());
        static::assertSame('/docker-dir', $process->getWorkingDirectory());
    }

    public function testCreateWithArgs(): void
    {
        $finder = $this->createMock(ExecutableFinder::class);
        $finder->expects($this->once())
            ->method('find')
            ->with('docker')
            ->willReturn('/usr/local/bin/docker');

        $factory = new DockerProcessFactory('/docker-dir', 'php8appreciate', '/composer/cache/dir', $finder);
        $input = new ProcessInput('php', ['one', 'two'], __DIR__, []);

        $process = $factory->create($input);
        $cmd  = "'/usr/local/bin/docker' 'compose' '-p' 'php8appreciate' '-f' '.docker/runtime/docker-compose.yml'";
        $cmd .= " 'run' '--rm' '-w' '/solution' 'runtime' 'php' 'one' 'two'";
        static::assertSame($cmd, $process->getCommandLine());
        static::assertSame('/docker-dir', $process->getWorkingDirectory());
    }

    public function testCreateWithEnv(): void
    {
        $finder = $this->createMock(ExecutableFinder::class);
        $finder->expects($this->once())
            ->method('find')
            ->with('docker')
            ->willReturn('/usr/local/bin/docker');

        $factory = new DockerProcessFactory('/docker-dir', 'php8appreciate', '/composer/cache/dir', $finder);
        $input = new ProcessInput('php', ['one', 'two'], __DIR__, ['SOME_VAR' => 'value']);

        $process = $factory->create($input);
        $cmd  = "'/usr/local/bin/docker' 'compose' '-p' 'php8appreciate' '-f' '.docker/runtime/docker-compose.yml'";
        $cmd .= " 'run' '--rm' '-e' 'SOME_VAR=value' '-w' '/solution' 'runtime' 'php' 'one' 'two'";
        static::assertSame($cmd, $process->getCommandLine());
        static::assertSame('/docker-dir', $process->getWorkingDirectory());
    }

    public function testWithInput(): void
    {
        $finder = $this->createMock(ExecutableFinder::class);
        $finder->expects($this->once())
            ->method('find')
            ->with('docker')
            ->willReturn('/usr/local/bin/docker');

        $factory = new DockerProcessFactory('/composer-dir', 'php8appreciate', '/composer/cache/dir', $finder);
        $input = new ProcessInput('php', [], __DIR__, [], 'someinput');

        $process = $factory->create($input);
        $cmd  = "'/usr/local/bin/docker' 'compose' '-p' 'php8appreciate' '-f' '.docker/runtime/docker-compose.yml'";
        $cmd .= " 'run' '--rm' '-w' '/solution' 'runtime' 'php'";
        static::assertSame($cmd, $process->getCommandLine());
        static::assertSame('someinput', $process->getInput());
    }

    public function testSolutionDirectoryIsPassedAsEnvVar(): void
    {
        $finder = $this->createMock(ExecutableFinder::class);
        $finder->expects($this->once())
            ->method('find')
            ->with('docker')
            ->willReturn('/usr/local/bin/docker');

        $factory = new DockerProcessFactory('/docker-dir', 'php8appreciate', '/composer/cache/dir', $finder);
        $input = new ProcessInput('php', ['one', 'two'], __DIR__, ['SOME_VAR' => 'value']);

        $process = $factory->create($input);
        $cmd  = "'/usr/local/bin/docker' 'compose' '-p' 'php8appreciate' '-f' '.docker/runtime/docker-compose.yml'";
        $cmd .= " 'run' '--rm' '-e' 'SOME_VAR=value' '-w' '/solution' 'runtime' 'php' 'one' 'two'";
        static::assertSame($cmd, $process->getCommandLine());
        static::assertSame('/docker-dir', $process->getWorkingDirectory());
        static::assertSame(['SOLUTION' => __DIR__], $process->getEnv());
    }
}
