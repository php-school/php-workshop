<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshopTest\Process;

use PhpSchool\PhpWorkshop\Process\Composer;
use PhpSchool\PhpWorkshop\Process\ProcessNotFoundException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\ExecutableFinder;

class ComposerTest extends TestCase
{
    public function testNeverRunsPackagePluginsOrScripts(): void
    {
        $finder = $this->createMock(ExecutableFinder::class);
        $finder->method('find')->with('composer')->willReturn('/usr/local/bin/composer');

        $process = (new Composer($finder))->create(['install'], __DIR__, ['COMPOSER_DISABLE_NETWORK' => '1']);

        static::assertSame(
            "'/usr/local/bin/composer' 'install' '--no-interaction' '--no-plugins' '--no-scripts'",
            $process->getCommandLine(),
        );
        static::assertSame(__DIR__, $process->getWorkingDirectory());
        static::assertSame(['COMPOSER_DISABLE_NETWORK' => '1'], $process->getEnv());
    }

    public function testThrowsIfComposerIsNotFound(): void
    {
        $finder = $this->createMock(ExecutableFinder::class);
        $finder->method('find')->with('composer')->willReturn(null);

        $this->expectException(ProcessNotFoundException::class);

        (new Composer($finder))->create(['install'], __DIR__);
    }
}
