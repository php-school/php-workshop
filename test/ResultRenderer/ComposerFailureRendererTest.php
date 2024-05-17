<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshopTest\ResultRenderer;

use PhpSchool\PhpWorkshop\Check\CheckInterface;
use PhpSchool\PhpWorkshop\Result\ComposerFailure;
use PhpSchool\PhpWorkshop\ResultRenderer\ComposerFailureRenderer;

class ComposerFailureRendererTest extends AbstractResultRendererTest
{
    /**
     * @dataProvider missingFileProvider
     */
    public function testRenderWithMissingFiles(string $file, string $message): void
    {
        $failure = new ComposerFailure(
            $this->createMock(CheckInterface::class),
            $file,
        );
        $renderer = new ComposerFailureRenderer($failure);

        $expected  = "$message\n";

        $this->assertEquals($expected, $renderer->render($this->getRenderer()));
    }

    public function missingFileProvider(): array
    {
        return [
            ['composer.json', '           No composer.json file found'],
            ['composer.lock', '           No composer.lock file found'],
            ['vendor', '              No vendor folder found'],
        ];
    }

    public function testRenderWithMissingPackages(): void
    {
        $failure = new ComposerFailure(
            $this->createMock(CheckInterface::class),
            null,
            ['some/package'],
        );
        $renderer = new ComposerFailureRenderer($failure);

        $expected  = "Lockfile doesn't include the following packages at any version: \"some/package\"\n";

        $this->assertEquals($expected, $renderer->render($this->getRenderer()));
    }

    public function testRenderWithMultipleMissingPackages(): void
    {
        $failure = new ComposerFailure(
            $this->createMock(CheckInterface::class),
            null,
            ['some/package', 'some-other/package'],
        );
        $renderer = new ComposerFailureRenderer($failure);

        $expected  = "Lockfile doesn't include the following packages at any version";
        $expected .= ": \"some/package\", \"some-other/package\"\n";

        $this->assertEquals($expected, $renderer->render($this->getRenderer()));
    }
}
