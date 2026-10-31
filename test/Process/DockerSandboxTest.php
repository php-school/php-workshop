<?php

namespace PhpSchool\PhpWorkshopTest\Process;

use PhpSchool\PhpWorkshop\Process\DockerExecution;
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
     * @param list<string> $mounts
     */
    private function expectedCreate(string $container, array $network, array $mounts = []): string
    {
        $parts = [
            '/usr/local/bin/docker', 'create', '--name', $container, '--init', '--user', getmyuid() . ':' . getmygid(),
            ...$network,
            '--read-only', '--tmpfs', '/tmp:rw,nosuid,nodev,size=64m', '-v', '/solution', '--cap-drop', 'ALL',
            '--security-opt', 'no-new-privileges', '--pids-limit', '64', '--memory', '256m', '--memory-swap', '256m',
            '--cpus', '1',
            ...array_merge(...array_map(fn($mount) => ['-v', $mount], $mounts)),
            'php8appreciate-runtime', 'sleep', 'infinity',
        ];

        return implode(' ', array_map(fn($p) => "'$p'", $parts));
    }

    /**
     * @param list<string> $env
     * @param list<string> $command
     */
    private function expectedExec(string $container, array $env, array $command): string
    {
        $parts = [
            '/usr/local/bin/docker', 'exec', '-i', ...$env, '-e', 'COMPOSER_HOME=/composer', '-w', '/solution',
            $container, 'timeout', '-s', 'KILL', '30',
            ...$command,
        ];

        return implode(' ', array_map(fn($p) => "'$p'", $parts));
    }

    private function container(DockerExecution $execution): string
    {
        preg_match('/php-school-[0-9a-f]{16}/', $execution->process->getCommandLine(), $matches);

        return $matches[0];
    }

    /**
     * @param list<string> $files
     */
    private function folder(array $files = ['solution.php']): string
    {
        $directory = sys_get_temp_dir() . '/' . bin2hex(random_bytes(4)) . '/' . bin2hex(random_bytes(4));
        foreach ($files as $file) {
            @mkdir(dirname($directory . '/' . $file), 0777, true);
            touch($directory . '/' . $file);
        }

        return $directory;
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
        $directory = $this->folder();
        $execution = $this->factory()->create(new ProcessInput('php', ['one', 'two'], $directory, [], []));
        $container = $this->container($execution);

        static::assertSame($this->expectedExec($container, [], ['php', 'one', 'two']), $execution->process->getCommandLine());
        static::assertSame('/docker-dir', $execution->process->getWorkingDirectory());
        static::assertSame(35.0, $execution->process->getTimeout());

        static::assertSame($this->expectedCreate($container, ['--network', 'none']), $execution->before[0]->getCommandLine());
        static::assertSame("'/usr/local/bin/docker' 'start' '$container'", $execution->before[1]->getCommandLine());
        static::assertSame(
            "tar -c -C '$directory' -- 'solution.php' | '/usr/local/bin/docker' exec -i $container tar -x -m -C /solution",
            $execution->before[2]->getCommandLine(),
        );
        static::assertSame([], $execution->after);
    }

    public function testRunsOfAFolderShareAContainerRemovedWithTheSandbox(): void
    {
        $finder = $this->anyFinder();
        $sandbox = new DockerSandbox('/docker-dir', 'php8appreciate', $finder);
        $directory = $this->folder();

        $first = $sandbox->create(new ProcessInput('php', ['one'], $directory, [], []));
        $second = $sandbox->create(new ProcessInput('php', ['two'], $directory, [], []));
        $other = $sandbox->create(new ProcessInput('php', ['one'], $this->folder(), [], []));

        static::assertSame($this->container($first), $this->container($second));
        static::assertSame([], $second->before);
        static::assertNotSame($this->container($first), $this->container($other));
    }

    public function testComposerGetsItsOwnContainerWithNetworkCacheAndNoPluginsOrScripts(): void
    {
        $directory = $this->folder(['composer.json']);
        $execution = $this->factory()->create(new ProcessInput('composer', ['install'], $directory, [], []));
        $container = $this->container($execution);

        static::assertSame(
            $this->expectedExec($container, [], ['composer', 'install', '--no-plugins', '--no-scripts']),
            $execution->process->getCommandLine(),
        );
        static::assertSame(
            $this->expectedCreate($container, ['--network', 'bridge'], ['php-school-composer-cache:/composer']),
            $execution->before[0]->getCommandLine(),
        );
        static::assertSame(
            "'/usr/local/bin/docker' exec $container tar -c -C /solution composer.lock vendor | tar -x -C '$directory'",
            $execution->after[0]->getCommandLine(),
        );
        static::assertSame("'/usr/local/bin/docker' 'rm' '-f' '-v' '$container'", $execution->after[1]->getCommandLine());
    }

    public function testVendorVolumeIsWritableForComposerOnly(): void
    {
        $directory = $this->folder(['composer.json', 'vendor/autoload.php']);
        $sandbox = new DockerSandbox('/docker-dir', 'php8appreciate', $this->anyFinder(), vendorVolumes: [dirname($directory) => 'vendor-student']);

        $composer = $sandbox->create(new ProcessInput('composer', ['update'], $directory, [], []));
        static::assertStringContainsString("'-v' 'vendor-student:/solution/vendor' ", $composer->before[0]->getCommandLine());
        //only the lock comes back, vendor stays in the volume
        static::assertSame(
            sprintf(
                "'/usr/local/bin/docker' exec %s tar -c -C /solution composer.lock  | tar -x -C '%s'; mkdir -p '%s/vendor'",
                $this->container($composer),
                $directory,
                $directory,
            ),
            $composer->after[0]->getCommandLine(),
        );

        $php = $sandbox->create(new ProcessInput('php', ['solution.php'], $directory, [], []));
        static::assertStringContainsString("'-v' 'vendor-student:/solution/vendor:ro' ", $php->before[0]->getCommandLine());
        static::assertStringContainsString("-- 'composer.json' |", $php->before[2]->getCommandLine());
    }

    public function testFoldersOutsideTheVendorVolumesKeepTheirOwnVendor(): void
    {
        $sandbox = new DockerSandbox('/docker-dir', 'php8appreciate', $this->anyFinder(), vendorVolumes: ['/tmp/student' => 'vendor-student']);

        $directory = $this->folder(['solution.php', 'vendor/autoload.php']);
        $execution = $sandbox->create(new ProcessInput('php', [], $directory, [], []));
        static::assertStringNotContainsString('vendor-student', $execution->before[0]->getCommandLine());
        //its contents, as tar can not change the image's vendor folder
        static::assertStringContainsString("-- 'solution.php' 'vendor/autoload.php' |", $execution->before[2]->getCommandLine());
    }

    private function anyFinder(): ExecutableFinder
    {
        $finder = $this->createMock(ExecutableFinder::class);
        $finder->method('find')->with('docker')->willReturn('/usr/local/bin/docker');

        return $finder;
    }

    public function testExposedPortsGetTheirOwnContainerPublishedOnLoopbackOnly(): void
    {
        $sandbox = $this->factory();
        $directory = $this->folder();
        $execution = $sandbox->create(new ProcessInput('php', [], $directory, [], [8000]));
        $container = $this->container($execution);

        static::assertSame(
            $this->expectedCreate($container, ['--network', 'bridge', '-p', '127.0.0.1:8000:8000']),
            $execution->before[0]->getCommandLine(),
        );
        static::assertSame("'/usr/local/bin/docker' 'rm' '-f' '-v' '$container'", $execution->after[0]->getCommandLine());

        $next = $sandbox->create(new ProcessInput('php', [], $directory, [], [8000]));
        static::assertNotSame($container, $this->container($next));
    }

    public function testExposedPortsUseTheGivenNetwork(): void
    {
        $execution = $this->factory('php-school-sandbox')->create(new ProcessInput('php', [], $this->folder(), [], [8000]));

        static::assertStringContainsString(
            "'--network' 'php-school-sandbox' '-p' '127.0.0.1:8000:8000'",
            $execution->before[0]->getCommandLine(),
        );
    }

    public function testExposedPortsArePublishedOnTheGivenHost(): void
    {
        $execution = $this->factory('bridge', '172.19.0.2')->create(new ProcessInput('php', [], $this->folder(), [], [8000]));

        static::assertStringContainsString("'-p' '172.19.0.2:8000:8000'", $execution->before[0]->getCommandLine());
    }

    public function testHostIsWherePortsArePublished(): void
    {
        $sandbox = new DockerSandbox('/docker-dir', 'php8appreciate', null, 'bridge', '172.19.0.2');

        static::assertSame('172.19.0.2', $sandbox->getHost()->getAddress());
    }

    public function testCreateWithEnv(): void
    {
        $execution = $this->factory()->create(new ProcessInput('php', [], $this->folder(), ['SOME_VAR' => 'value'], []));

        static::assertSame(
            $this->expectedExec($this->container($execution), ['-e', 'SOME_VAR=value'], ['php']),
            $execution->process->getCommandLine(),
        );
    }

    public function testWithInput(): void
    {
        $process = $this->factory()->create(new ProcessInput('php', [], $this->folder(), [], [], 'someinput'))->process;

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
