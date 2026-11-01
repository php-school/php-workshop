<?php

namespace PhpSchool\PhpWorkshopTest\Process;

use PhpSchool\PhpWorkshop\Process\DockerSandboxFactory;
use PhpSchool\PhpWorkshop\Process\Port\PortPicker;
use PhpSchool\PhpWorkshop\Exercise\Scenario\CliScenario;
use PhpSchool\PhpWorkshop\Process\ProcessInput;
use PhpSchool\PhpWorkshop\Process\ProcessNotFoundException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\ExecutableFinder;

class DockerSandboxTest extends TestCase
{
    private function factory(string $portsNetwork = 'bridge', string $portsHost = '127.0.0.1'): DockerSandboxFactory
    {
        $ports = new class implements PortPicker {
            public function pick(): int
            {
                return 47213;
            }
        };

        $finder = $this->createMock(ExecutableFinder::class);
        $finder->expects($this->once())
            ->method('find')
            ->with('docker')
            ->willReturn('/usr/local/bin/docker');

        return new DockerSandboxFactory('/docker-dir', 'PHP8Appreciate', $finder, $portsNetwork, $portsHost, $ports);
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

        $factory = new DockerSandboxFactory('/docker-dir', 'php8appreciate', $finder);
        $input = new ProcessInput('php', [], []);

        $factory->create(__DIR__, new CliScenario())->execute($input);
    }

    public function testCreateIsIsolatedWithNoNetwork(): void
    {
        $process = $this->factory()->create(__DIR__, new CliScenario())->execute(new ProcessInput('php', ['one', 'two'], []))->process;

        $expected = $this->expectedCommand(
            ['--network', 'none'],
            ['-v', __DIR__ . ':/solution'],
            ['php', 'one', 'two'],
        );
        static::assertSame($expected, $process->getCommandLine());
        static::assertSame('/docker-dir', $process->getWorkingDirectory());
        static::assertSame(35.0, $process->getTimeout());
    }

    public function testExposedPortsArePublishedOnLoopbackOnly(): void
    {
        $process = $this->factory()->create(__DIR__, (new CliScenario())->exposePort())->execute(new ProcessInput('php', [], []))->process;

        $expected = $this->expectedCommand(
            ['--network', 'bridge', '-p', '127.0.0.1:47213:47213'],
            ['-v', __DIR__ . ':/solution'],
            ['php'],
        );
        static::assertSame($expected, $process->getCommandLine());
    }

    public function testExposedPortsUseTheGivenNetwork(): void
    {
        $process = $this->factory('php-school-sandbox')->create(__DIR__, (new CliScenario())->exposePort())->execute(new ProcessInput('php', [], []))->process;

        $expected = $this->expectedCommand(
            ['--network', 'php-school-sandbox', '-p', '127.0.0.1:47213:47213'],
            ['-v', __DIR__ . ':/solution'],
            ['php'],
        );
        static::assertSame($expected, $process->getCommandLine());
    }

    public function testExposedPortsArePublishedOnTheGivenHost(): void
    {
        $process = $this->factory('bridge', '172.19.0.2')->create(__DIR__, (new CliScenario())->exposePort())->execute(new ProcessInput('php', [], []))->process;

        $expected = $this->expectedCommand(
            ['--network', 'bridge', '-p', '172.19.0.2:47213:47213'],
            ['-v', __DIR__ . ':/solution'],
            ['php'],
        );
        static::assertSame($expected, $process->getCommandLine());
    }

    public function testPortsArePickedWithThePortPicker(): void
    {
        $ports = $this->createMock(PortPicker::class);
        $ports->method('pick')->willReturn(47213);

        static::assertSame(47213, (new DockerSandboxFactory('/docker-dir', 'php8appreciate', portPicker: $ports))->create(__DIR__, (new CliScenario())->exposePort())->getPort());
    }

    public function testAddressIsTheSamePortOnTheGivenHost(): void
    {
        $execution = $this->factory('bridge', '172.19.0.2')->create(__DIR__, (new CliScenario())->exposePort())->execute(new ProcessInput('php', [], []));
        static::assertSame('172.19.0.2:47213', $execution->getAddress());

        $execution = $this->factory('bridge', '[fdaa::2]')->create(__DIR__, (new CliScenario())->exposePort())->execute(new ProcessInput('php', [], []));
        static::assertStringContainsString("'-p' '[fdaa::2]:47213:47213'", $execution->process->getCommandLine());
        static::assertSame('[fdaa::2]:47213', $execution->getAddress());
    }

    public function testCreateWithEnv(): void
    {
        $process = $this->factory()->create(__DIR__, new CliScenario())->execute(new ProcessInput('php', [], ['SOME_VAR' => 'value']))->process;

        $expected = $this->expectedCommand(
            ['--network', 'none'],
            ['-e', 'SOME_VAR=value', '-v', __DIR__ . ':/solution'],
            ['php'],
        );
        static::assertSame($expected, $process->getCommandLine());
    }

    public function testWithInput(): void
    {
        $process = $this->factory()->create(__DIR__, new CliScenario())->execute(new ProcessInput('php', [], [], 'someinput'))->process;

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
