<?php

namespace PhpSchool\PhpWorkshopTest\Process\Sandbox\Docker;

use PhpSchool\PhpWorkshop\ExerciseRunner\Context\TestContext;
use PhpSchool\PhpWorkshop\Process\Sandbox\Docker\DockerSandboxFactory;
use PhpSchool\PhpWorkshop\Process\Port\PortPicker;
use PhpSchool\PhpWorkshop\Exercise\Scenario\CliScenario;
use PhpSchool\PhpWorkshop\Process\Sandbox\ProcessInput;
use PhpSchool\PhpWorkshop\Process\ProcessNotFoundException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\ExecutableFinder;

class DockerSandboxTest extends TestCase
{
    private const DOCKER = __DIR__ . '/../../../res/fake-docker';

    private string $log;

    /**
     * @var list<int>
     */
    private array $released = [];

    public function setUp(): void
    {
        $this->log = (string) tempnam(sys_get_temp_dir(), 'docker-log');
        //Process only passes on env vars that are in $_SERVER too
        putenv('FAKE_DOCKER_LOG=' . $this->log);
        $_SERVER['FAKE_DOCKER_LOG'] = $this->log;
    }

    public function tearDown(): void
    {
        putenv('FAKE_DOCKER_LOG');
        unset($_SERVER['FAKE_DOCKER_LOG']);
        unlink($this->log);
    }

    private function factory(string $portsNetwork = 'bridge', string $portsHost = '127.0.0.1'): DockerSandboxFactory
    {
        $ports = $this->createMock(PortPicker::class);
        $ports->method('pick')->willReturn(47213);
        $ports->method('release')->willReturnCallback(function (int $port) {
            $this->released[] = $port;
        });

        $finder = $this->createMock(ExecutableFinder::class);
        $finder->method('find')->with('docker')->willReturn(self::DOCKER);

        return new DockerSandboxFactory('/docker-dir', 'PHP8Appreciate', $finder, $portsNetwork, $portsHost, $ports);
    }

    /**
     * What the fake docker was run with, the random container name as C.
     *
     * @return list<string> eg. ['run -d --rm --name C ...', 'cp /dir/. C:/solution']
     */
    private function dockerCalls(): array
    {
        return array_map(
            fn($line) => (string) preg_replace('/php-school-[0-9a-f]{16}/', 'C', $line),
            file($this->log, FILE_IGNORE_NEW_LINES) ?: [],
        );
    }

    private function expectedRun(string $network): string
    {
        return implode(' ', [
            'run -d --rm --name C --init --user', getmyuid() . ':' . getmygid(), $network,
            '--read-only --tmpfs /tmp:rw,nosuid,nodev,size=64m --cap-drop ALL --security-opt no-new-privileges',
            '--pids-limit 64 --memory 256m --memory-swap 256m --cpus 1 -v /solution -w /solution',
            'php8appreciate-runtime sleep 300',
        ]);
    }

    public function testCreateThrowsExceptionIfDockerNotFound(): void
    {
        static::expectException(ProcessNotFoundException::class);

        $finder = $this->createMock(ExecutableFinder::class);
        $finder->expects($this->once())
            ->method('find')
            ->with('docker')
            ->willReturn(null);

        (new DockerSandboxFactory('/docker-dir', 'php8appreciate', $finder))->create(__DIR__, new CliScenario());
    }

    public function testCreateStartsAnIsolatedContainerWithNoNetworkAndCopiesTheDirectoryIn(): void
    {
        $this->factory()->create(__DIR__, new CliScenario());

        static::assertSame([$this->expectedRun('--network none'), 'cp ' . __DIR__ . '/. C:/solution'], $this->dockerCalls());
    }

    public function testExposedPortIsPublishedTheSameOnTheGivenNetworkAndHost(): void
    {
        $this->factory('php-school-sandbox', '[fdaa::2]')->create(__DIR__, (new CliScenario())->exposePort());

        static::assertSame($this->expectedRun('--network php-school-sandbox -p [fdaa::2]:47213:47213'), $this->dockerCalls()[0]);
    }

    public function testExecuteIsAnExecInTheContainer(): void
    {
        $sandbox = $this->factory()->create(__DIR__, new CliScenario());
        $process = $sandbox->execute(new ProcessInput('php', ['one', 'two'], ['SOME_VAR' => 'value'], 'someinput'))->process;

        static::assertSame(
            "'" . self::DOCKER . "' 'exec' '-i' '-e' 'SOME_VAR=value' 'C' 'timeout' '-s' 'KILL' '30' 'php' 'one' 'two'",
            preg_replace('/php-school-[0-9a-f]{16}/', 'C', $process->getCommandLine()),
        );
        static::assertSame('someinput', $process->getInput());
        static::assertSame(35.0, $process->getTimeout());
    }

    public function testAddressIsTheSamePortOnTheGivenHost(): void
    {
        $sandbox = $this->factory('bridge', '172.19.0.2')->create(__DIR__, (new CliScenario())->exposePort());

        static::assertSame(47213, $sandbox->getPort());
        static::assertSame('172.19.0.2:47213', $sandbox->execute(new ProcessInput('php', [], []))->getAddress());
    }

    public function testFinishCopiesTheFilesOutRemovesTheContainerAndReleasesThePort(): void
    {
        $this->factory()->create(__DIR__, (new CliScenario())->exposePort())->finish(new TestContext());

        static::assertSame(
            ['exec C tar -c -C /solution --exclude=./vendor .', 'rm -f -v C'],
            array_slice($this->dockerCalls(), 2),
        );
        static::assertSame([47213], $this->released);
    }

    public function testCreateBuildProcess(): void
    {
        $process = $this->factory()->createBuildProcess();

        static::assertSame(
            "'" . self::DOCKER . "' 'build' '-t' 'php8appreciate-runtime' '.docker/runtime'",
            $process->getCommandLine(),
        );
        static::assertSame('/docker-dir', $process->getWorkingDirectory());
    }
}
