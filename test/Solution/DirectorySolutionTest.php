<?php

namespace PhpSchool\PhpWorkshopTest\Solution;

use InvalidArgumentException;
use PhpSchool\PhpWorkshop\Solution\DirectorySolution;
use PhpSchool\PhpWorkshop\Utils\System;
use PhpSchool\PhpWorkshopTest\BaseTest;
use Symfony\Component\Filesystem\Filesystem;

class DirectorySolutionTest extends BaseTest
{
    public function tearDown(): void
    {
        (new Filesystem())->remove(System::tempDir('php-school'));

        parent::tearDown();
    }

    public function testExceptionIsThrownIfEntryPointDoesNotExist(): void
    {
        $this->getTemporaryFile('some-class.php');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/Entry point: "solution.php" does not exist in: ".*"/');

        DirectorySolution::fromDirectory($this->getTemporaryDirectory());
    }

    public function testWithDefaultEntryPoint(): void
    {
        $this->getTemporaryFile('solution.php', 'ENTRYPOINT');
        $this->getTemporaryFile('some-class.php', 'SOME CLASS');

        $solution = DirectorySolution::fromDirectory($this->getTemporaryDirectory());

        self::assertFalse($solution->hasComposerFile());
        self::assertSame('ENTRYPOINT', file_get_contents($solution->getEntryPoint()));
        $files = $solution->getFiles();
        self::assertCount(2, $files);

        self::assertSame('ENTRYPOINT', file_get_contents($files[0]->__toString()));
        self::assertSame('SOME CLASS', file_get_contents($files[1]->__toString()));
    }

    public function testWithManualEntryPoint(): void
    {
        $this->getTemporaryFile('index.php', 'ENTRYPOINT');
        $this->getTemporaryFile('some-class.php', 'SOME CLASS');

        $solution = DirectorySolution::fromDirectory($this->getTemporaryDirectory(), [], 'index.php');

        self::assertFalse($solution->hasComposerFile());
        self::assertSame('ENTRYPOINT', file_get_contents($solution->getEntryPoint()));
        $files = $solution->getFiles();
        self::assertCount(2, $files);

        self::assertSame('ENTRYPOINT', file_get_contents($files[0]->__toString()));
        self::assertSame('SOME CLASS', file_get_contents($files[1]->__toString()));
    }

    public function testHasComposerFileReturnsTrueIfPresent(): void
    {
        $this->getTemporaryFile('solution.php', 'ENTRYPOINT');
        $this->getTemporaryFile('some-class.php', 'SOME CLASS');
        $this->getTemporaryFile('composer.json', 'composer');

        $solution = DirectorySolution::fromDirectory($this->getTemporaryDirectory());

        self::assertTrue($solution->hasComposerFile());
        self::assertSame('ENTRYPOINT', file_get_contents($solution->getEntryPoint()));
        $files = $solution->getFiles();
        self::assertCount(3, $files);

        self::assertSame('composer', file_get_contents($files[0]->__toString()));
        self::assertSame('ENTRYPOINT', file_get_contents($files[1]->__toString()));
        self::assertSame('SOME CLASS', file_get_contents($files[2]->__toString()));
    }

    public function testWithExceptions(): void
    {
        $this->getTemporaryFile('solution.php', 'ENTRYPOINT');
        $this->getTemporaryFile('some-class.php', 'SOME CLASS');
        $this->getTemporaryFile('exclude.txt');

        $exclusions = ['exclude.txt'];

        $solution = DirectorySolution::fromDirectory($this->getTemporaryDirectory(), $exclusions);

        self::assertSame('ENTRYPOINT', file_get_contents($solution->getEntryPoint()));
        $files = $solution->getFiles();
        self::assertCount(2, $files);

        self::assertSame('ENTRYPOINT', file_get_contents($files[0]->__toString()));
        self::assertSame('SOME CLASS', file_get_contents($files[1]->__toString()));
    }

    public function testWithNestedDirectories(): void
    {
        $this->getTemporaryFile('solution.php', 'ENTRYPOINT');
        $this->getTemporaryFile('some-class.php', 'SOME CLASS');
        $this->getTemporaryFile('composer.json', 'COMPOSER DATA');
        $this->getTemporaryFile('nested/another-class.php', 'ANOTHER CLASS');
        $this->getTemporaryFile('nested/deep/even-more.php', 'EVEN MOAR');

        $solution = DirectorySolution::fromDirectory($this->getTemporaryDirectory());

        self::assertSame('ENTRYPOINT', file_get_contents($solution->getEntryPoint()));
        $files = $solution->getFiles();
        self::assertCount(5, $files);

        self::assertSame('COMPOSER DATA', file_get_contents($files[0]->__toString()));
        self::assertSame('ANOTHER CLASS', file_get_contents($files[1]->__toString()));
        self::assertSame('EVEN MOAR', file_get_contents($files[2]->__toString()));
        self::assertSame('ENTRYPOINT', file_get_contents($files[3]->__toString()));
        self::assertSame('SOME CLASS', file_get_contents($files[4]->__toString()));
    }

    public function testExceptionsWithNestedDirectories(): void
    {
        $this->getTemporaryFile('solution.php', 'ENTRYPOINT');
        $this->getTemporaryFile('some-class.php', 'SOME CLASS');
        $this->getTemporaryFile('exclude.txt');
        $this->getTemporaryFile('nested/exclude.txt');
        $this->getTemporaryFile('nested/deep/exclude.txt');
        $this->getTemporaryFile('vendor/somelib/app.php');

        $exclusions = ['exclude.txt', 'vendor'];

        $solution = DirectorySolution::fromDirectory($this->getTemporaryDirectory(), $exclusions);

        self::assertSame('ENTRYPOINT', file_get_contents($solution->getEntryPoint()));
        $files = $solution->getFiles();
        self::assertCount(2, $files);

        self::assertSame('ENTRYPOINT', file_get_contents($files[0]->__toString()));
        self::assertSame('SOME CLASS', file_get_contents($files[1]->__toString()));
    }
}
