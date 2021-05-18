<?php

namespace PhpSchool\PhpWorkshopTest\Solution;

use InvalidArgumentException;
use PhpSchool\PhpWorkshop\Exercise\TemporaryDirectoryTrait;
use PhpSchool\PhpWorkshop\Solution\DirectorySolution;
use PhpSchool\PhpWorkshop\Utils\Path;
use PhpSchool\PhpWorkshop\Utils\System;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

class DirectorySolutionTest extends TestCase
{
    /**
     * @var string
     */
    private $tempPath;

    public function setUp(): void
    {
        $this->tempPath = System::tempDir($this->getName());
        @mkdir($this->tempPath);
    }

    public function tearDown(): void
    {
        $fileSystem = new Filesystem();

        $fileSystem->remove(System::tempDir('php-school'));
        $fileSystem->remove($this->tempPath);
    }

    public function testExceptionIsThrownIfEntryPointDoesNotExist(): void
    {
        touch(sprintf('%s/some-class.php', $this->tempPath));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/Entry point: "solution.php" does not exist in: ".*"/');

        DirectorySolution::fromDirectory($this->tempPath);
    }

    public function testWithDefaultEntryPoint(): void
    {
        file_put_contents(sprintf('%s/solution.php', $this->tempPath), 'ENTRYPOINT');
        file_put_contents(sprintf('%s/some-class.php', $this->tempPath), 'SOME CLASS');

        $solution = DirectorySolution::fromDirectory($this->tempPath);

        self::assertFalse($solution->hasComposerFile());
        self::assertSame('ENTRYPOINT', file_get_contents($solution->getEntryPoint()));
        $files = $solution->getFiles();
        self::assertCount(2, $files);

        self::assertSame('ENTRYPOINT', file_get_contents($files[0]->__toString()));
        self::assertSame('SOME CLASS', file_get_contents($files[1]->__toString()));
    }

    public function testWithManualEntryPoint(): void
    {
        file_put_contents(sprintf('%s/index.php', $this->tempPath), 'ENTRYPOINT');
        file_put_contents(sprintf('%s/some-class.php', $this->tempPath), 'SOME CLASS');

        $solution = DirectorySolution::fromDirectory($this->tempPath, [], 'index.php');

        self::assertFalse($solution->hasComposerFile());
        self::assertSame('ENTRYPOINT', file_get_contents($solution->getEntryPoint()));
        $files = $solution->getFiles();
        self::assertCount(2, $files);

        self::assertSame('ENTRYPOINT', file_get_contents($files[0]->__toString()));
        self::assertSame('SOME CLASS', file_get_contents($files[1]->__toString()));
    }

    public function testHasComposerFileReturnsTrueIfPresent(): void
    {
        file_put_contents(sprintf('%s/solution.php', $this->tempPath), 'ENTRYPOINT');
        file_put_contents(sprintf('%s/some-class.php', $this->tempPath), 'SOME CLASS');
        touch(sprintf('%s/composer.lock', $this->tempPath));

        $solution = DirectorySolution::fromDirectory($this->tempPath);

        self::assertTrue($solution->hasComposerFile());
        self::assertSame('ENTRYPOINT', file_get_contents($solution->getEntryPoint()));
        $files = $solution->getFiles();
        self::assertCount(2, $files);

        self::assertSame('ENTRYPOINT', file_get_contents($files[0]->__toString()));
        self::assertSame('SOME CLASS', file_get_contents($files[1]->__toString()));
    }

    public function testWithExceptions(): void
    {
        file_put_contents(sprintf('%s/solution.php', $this->tempPath), 'ENTRYPOINT');
        file_put_contents(sprintf('%s/some-class.php', $this->tempPath), 'SOME CLASS');
        touch(sprintf('%s/exclude.txt', $this->tempPath));

        $exclusions = ['exclude.txt'];

        $solution = DirectorySolution::fromDirectory($this->tempPath, $exclusions);

        self::assertSame('ENTRYPOINT', file_get_contents($solution->getEntryPoint()));
        $files = $solution->getFiles();
        self::assertCount(2, $files);

        self::assertSame('ENTRYPOINT', file_get_contents($files[0]->__toString()));
        self::assertSame('SOME CLASS', file_get_contents($files[1]->__toString()));
    }

    public function testWithNestedDirectories(): void
    {
        @mkdir(sprintf('%s/nested', $this->tempPath), 0775, true);
        @mkdir(sprintf('%s/nested/deep', $this->tempPath), 0775, true);

        file_put_contents(sprintf('%s/solution.php', $this->tempPath), 'ENTRYPOINT');
        file_put_contents(sprintf('%s/some-class.php', $this->tempPath), 'SOME CLASS');
        file_put_contents(sprintf('%s/composer.json', $this->tempPath), 'COMPOSER DATA');
        file_put_contents(sprintf('%s/nested/another-class.php', $this->tempPath), 'ANOTHER CLASS');
        file_put_contents(sprintf('%s/nested/deep/even-more.php', $this->tempPath), 'EVEN MOAR');

        $solution = DirectorySolution::fromDirectory($this->tempPath);

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
        @mkdir(sprintf('%s/nested', $this->tempPath), 0775, true);
        @mkdir(sprintf('%s/nested/deep', $this->tempPath), 0775, true);
        @mkdir(sprintf('%s/vendor', $this->tempPath), 0775, true);
        @mkdir(sprintf('%s/vendor/somelib', $this->tempPath), 0775, true);

        file_put_contents(sprintf('%s/solution.php', $this->tempPath), 'ENTRYPOINT');
        file_put_contents(sprintf('%s/some-class.php', $this->tempPath), 'SOME CLASS');
        touch(sprintf('%s/exclude.txt', $this->tempPath));
        touch(sprintf('%s/nested/exclude.txt', $this->tempPath));
        touch(sprintf('%s/nested/deep/exclude.txt', $this->tempPath));
        touch(sprintf('%s/vendor/somelib/app.php', $this->tempPath));

        $exclusions = ['exclude.txt', 'vendor'];

        $solution = DirectorySolution::fromDirectory($this->tempPath, $exclusions);

        self::assertSame('ENTRYPOINT', file_get_contents($solution->getEntryPoint()));
        $files = $solution->getFiles();
        self::assertCount(2, $files);

        self::assertSame('ENTRYPOINT', file_get_contents($files[0]->__toString()));
        self::assertSame('SOME CLASS', file_get_contents($files[1]->__toString()));
    }
}
