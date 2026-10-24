<?php

namespace PhpSchool\PhpWorkshopTest\Process;

use PhpSchool\PhpWorkshop\Process\DockerProcessFactory;
use PhpSchool\PhpWorkshop\Process\ProcessInput;
use PhpSchool\PhpWorkshop\Process\ProcessNotFoundException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\ExecutableFinder;

class DockerProcessFactoryTest extends TestCase
{
    private function factory(string $portsNetwork = 'bridge'): DockerProcessFactory
    {
        $finder = $this->createMock(ExecutableFinder::class);
        $finder->expects($this->once())
            ->method('find')
            ->with('docker')
            ->willReturn('/usr/local/bin/docker');

        return new DockerProcessFactory('/docker-dir', 'PHP8Appreciate', '/composer/cache/dir', $finder, $portsNetwork);
    }

    /**
     * @param list<string> $network
     * @param list<string> $extra env and mount args
     * @param list<string> $command
     */
    private function expectedCommand(array $network, array $extra, array $command): string
    {
        $parts = [
            '/usr/local/bin/docker', 'run', '--rm', '-i', '--init', '--user', getmyuid() . ':' . getmygid(),
            ...$network,
            '--read-only', '--tmpfs', '/tmp:rw,nosuid,nodev,size=64m', '--cap-drop', 'ALL',
            '--security-opt', 'no-new-privileges', '--pids-limit', '64', '--memory', '256m', '--memory-swap', '256m',
            '--cpus', '1',
            ...$extra,
            '-w', '/solution', 'php8appreciate-runtime', 'timeout', '-s', 'KILL', '30',
            ...$command,
        ];

        return implode(' ', array_map(fn($p) => "'$p'", $parts));
    }

    public function testCreateThrowsExceptionIfDockerNotFound(): void
    {
        static::expectException(ProcessNotFoundException::class);

        $finder = $this->createMock(ExecutableFinder::class);
        $finder->expects($this->once())
            ->method('find')
            ->with('docker')
            ->willReturn(null);

        $factory = new DockerProcessFactory('/docker-dir', 'php8appreciate', '/composer/cache/dir', $finder);
        $input = new ProcessInput('composer', [], __DIR__, [], []);

        $factory->create($input);
    }

    public function testCreateIsIsolatedWithNoNetwork(): void
    {
        $process = $this->factory()->create(new ProcessInput('php', ['one', 'two'], __DIR__, [], []));

        $expected = $this->expectedCommand(
            ['--network', 'none'],
            ['-e', 'COMPOSER_HOME=/tmp/composer', '-v', __DIR__ . ':/solution'],
            ['php', 'one', 'two'],
        );
        static::assertSame($expected, $process->getCommandLine());
        static::assertSame('/docker-dir', $process->getWorkingDirectory());
        static::assertSame(35.0, $process->getTimeout());
    }

    public function testComposerGetsNetworkCacheAndNoPluginsOrScripts(): void
    {
        $process = $this->factory()->create(new ProcessInput('composer', ['install'], __DIR__, [], []));

        $expected = $this->expectedCommand(
            ['--network', 'bridge'],
            ['-e', 'COMPOSER_HOME=/tmp/composer', '-v', __DIR__ . ':/solution', '-v', '/composer/cache/dir:/tmp/composer'],
            ['composer', 'install', '--no-plugins', '--no-scripts'],
        );
        static::assertSame($expected, $process->getCommandLine());
    }

    public function testExposedPortsArePublishedOnLoopbackOnly(): void
    {
        $process = $this->factory()->create(new ProcessInput('php', [], __DIR__, [], [8000]));

        $expected = $this->expectedCommand(
            ['--network', 'bridge', '-p', '127.0.0.1:8000:8000'],
            ['-e', 'COMPOSER_HOME=/tmp/composer', '-v', __DIR__ . ':/solution'],
            ['php'],
        );
        static::assertSame($expected, $process->getCommandLine());
    }

    public function testExposedPortsUseTheGivenNetwork(): void
    {
        $process = $this->factory('php-school-sandbox')->create(new ProcessInput('php', [], __DIR__, [], [8000]));

        $expected = $this->expectedCommand(
            ['--network', 'php-school-sandbox', '-p', '127.0.0.1:8000:8000'],
            ['-e', 'COMPOSER_HOME=/tmp/composer', '-v', __DIR__ . ':/solution'],
            ['php'],
        );
        static::assertSame($expected, $process->getCommandLine());
    }

    public function testCreateWithEnv(): void
    {
        $process = $this->factory()->create(new ProcessInput('php', [], __DIR__, ['SOME_VAR' => 'value'], []));

        $expected = $this->expectedCommand(
            ['--network', 'none'],
            ['-e', 'SOME_VAR=value', '-e', 'COMPOSER_HOME=/tmp/composer', '-v', __DIR__ . ':/solution'],
            ['php'],
        );
        static::assertSame($expected, $process->getCommandLine());
    }

    public function testWithInput(): void
    {
        $process = $this->factory()->create(new ProcessInput('php', [], __DIR__, [], [], 'someinput'));

        static::assertSame('someinput', $process->getInput());
    }

    public function testCreateBuildProcess(): void
    {
        $process = $this->factory()->createBuildProcess();

        static::assertSame(
            "'/usr/local/bin/docker' 'build' '-t' 'php8appreciate-runtime' '.docker/runtime'",
            $process->getCommandLine(),
        );
        static::assertSame('/docker-dir', $process->getWorkingDirectory());
    }
}
