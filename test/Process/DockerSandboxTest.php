<?php

namespace PhpSchool\PhpWorkshopTest\Process;

use PhpSchool\PhpWorkshop\Process\DockerSandbox;
use PhpSchool\PhpWorkshop\Process\ProcessInput;
use PhpSchool\PhpWorkshop\Process\ProcessNotFoundException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\ExecutableFinder;

class DockerSandboxTest extends TestCase
{
    private function factory(string $portsNetwork = 'bridge', string $portsHost = '127.0.0.1'): DockerSandbox
    {
        $finder = $this->createMock(ExecutableFinder::class);
        $finder->expects($this->once())
            ->method('find')
            ->with('docker')
            ->willReturn('/usr/local/bin/docker');

        return new DockerSandbox('/docker-dir', 'PHP8Appreciate', $finder, $portsNetwork, $portsHost);
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

        $factory = new DockerSandbox('/docker-dir', 'php8appreciate', $finder);
        $input = new ProcessInput('composer', [], __DIR__, [], []);

        $factory->create($input);
    }

    public function testCreateIsIsolatedWithNoNetwork(): void
    {
        $process = $this->factory()->create(new ProcessInput('php', ['one', 'two'], __DIR__, [], []))->process;

        $expected = $this->expectedCommand(
            ['--network', 'none'],
            ['-e', 'COMPOSER_HOME=/composer', '-v', __DIR__ . ':/solution'],
            ['php', 'one', 'two'],
        );
        static::assertSame($expected, $process->getCommandLine());
        static::assertSame('/docker-dir', $process->getWorkingDirectory());
        static::assertSame(35.0, $process->getTimeout());
    }

    public function testComposerGetsNetworkCacheAndNoPluginsOrScripts(): void
    {
        $process = $this->factory()->create(new ProcessInput('composer', ['install'], __DIR__, [], []))->process;

        $expected = $this->expectedCommand(
            ['--network', 'bridge'],
            ['-e', 'COMPOSER_HOME=/composer', '-v', __DIR__ . ':/solution', '-v', 'php-school-composer-cache:/composer'],
            ['composer', 'install', '--no-plugins', '--no-scripts'],
        );
        static::assertSame($expected, $process->getCommandLine());
    }

    public function testVendorVolumeIsWritableForComposerOnly(): void
    {
        $sandbox = new DockerSandbox('/docker-dir', 'php8appreciate', $this->anyFinder(), vendorVolumes: ['/tmp/student/' => 'vendor-student']);

        $composer = $sandbox->create(new ProcessInput('composer', ['update'], '/tmp/student/abc', [], []))->process;
        static::assertStringContainsString("'-v' 'vendor-student:/solution/vendor' ", $composer->getCommandLine());

        $php = $sandbox->create(new ProcessInput('php', ['solution.php'], '/tmp/student/def', [], []))->process;
        static::assertStringContainsString("'-v' 'vendor-student:/solution/vendor:ro' ", $php->getCommandLine());
    }

    public function testFoldersOutsideTheVendorVolumesKeepTheirOwnVendor(): void
    {
        $sandbox = new DockerSandbox('/docker-dir', 'php8appreciate', $this->anyFinder(), vendorVolumes: ['/tmp/student' => 'vendor-student']);

        //eg. the reference solution, or a sibling folder whose name starts the same
        foreach (['/tmp/reference', '/tmp/student-2'] as $directory) {
            $process = $sandbox->create(new ProcessInput('composer', ['install'], $directory, [], []))->process;
            static::assertStringNotContainsString('vendor-student', $process->getCommandLine());
        }
    }

    private function anyFinder(): ExecutableFinder
    {
        $finder = $this->createMock(ExecutableFinder::class);
        $finder->method('find')->with('docker')->willReturn('/usr/local/bin/docker');

        return $finder;
    }

    public function testExposedPortsArePublishedOnLoopbackOnly(): void
    {
        $process = $this->factory()->create(new ProcessInput('php', [], __DIR__, [], [8000]))->process;

        $expected = $this->expectedCommand(
            ['--network', 'bridge', '-p', '127.0.0.1:8000:8000'],
            ['-e', 'COMPOSER_HOME=/composer', '-v', __DIR__ . ':/solution'],
            ['php'],
        );
        static::assertSame($expected, $process->getCommandLine());
    }

    public function testExposedPortsUseTheGivenNetwork(): void
    {
        $process = $this->factory('php-school-sandbox')->create(new ProcessInput('php', [], __DIR__, [], [8000]))->process;

        $expected = $this->expectedCommand(
            ['--network', 'php-school-sandbox', '-p', '127.0.0.1:8000:8000'],
            ['-e', 'COMPOSER_HOME=/composer', '-v', __DIR__ . ':/solution'],
            ['php'],
        );
        static::assertSame($expected, $process->getCommandLine());
    }

    public function testExposedPortsArePublishedOnTheGivenHost(): void
    {
        $process = $this->factory('bridge', '172.19.0.2')->create(new ProcessInput('php', [], __DIR__, [], [8000]))->process;

        $expected = $this->expectedCommand(
            ['--network', 'bridge', '-p', '172.19.0.2:8000:8000'],
            ['-e', 'COMPOSER_HOME=/composer', '-v', __DIR__ . ':/solution'],
            ['php'],
        );
        static::assertSame($expected, $process->getCommandLine());
    }

    public function testHostIsWherePortsArePublished(): void
    {
        $sandbox = new DockerSandbox('/docker-dir', 'php8appreciate', null, 'bridge', '172.19.0.2');

        static::assertSame('172.19.0.2', $sandbox->getHost()->getAddress());
    }

    public function testCreateWithEnv(): void
    {
        $process = $this->factory()->create(new ProcessInput('php', [], __DIR__, ['SOME_VAR' => 'value'], []))->process;

        $expected = $this->expectedCommand(
            ['--network', 'none'],
            ['-e', 'SOME_VAR=value', '-e', 'COMPOSER_HOME=/composer', '-v', __DIR__ . ':/solution'],
            ['php'],
        );
        static::assertSame($expected, $process->getCommandLine());
    }

    public function testWithInput(): void
    {
        $process = $this->factory()->create(new ProcessInput('php', [], __DIR__, [], [], 'someinput'))->process;

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
