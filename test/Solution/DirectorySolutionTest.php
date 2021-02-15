<?php

namespace PhpSchool\PhpWorkshopTest\Solution;

use InvalidArgumentException;
use PhpSchool\PhpWorkshop\Solution\DirectorySolution;
use PhpSchool\PhpWorkshop\TestUtils\SolutionPathTransformer;
use PHPUnit\Framework\TestCase;

class DirectorySolutionTest extends TestCase
{
    public function testExceptionIsThrownIfEntryPointDoesNotExist(): void
    {
        $tempPath = sprintf('%s/%s', realpath(sys_get_temp_dir()), $this->getName());
        @mkdir($tempPath, 0775, true);
        touch(sprintf('%s/some-class.php', $tempPath));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('Entry point: "solution.php" does not exist in: "%s"', $tempPath));

        DirectorySolution::fromDirectory($tempPath);

        unlink(sprintf('%s/some-class.php', $tempPath));
        rmdir($tempPath);
    }

    public function testWithDefaultEntryPoint(): void
    {
        $tempPath = sprintf('%s/%s', realpath(sys_get_temp_dir()), $this->getName());
        @mkdir($tempPath, 0775, true);
        touch(sprintf('%s/solution.php', $tempPath));
        touch(sprintf('%s/some-class.php', $tempPath));

        $solution = DirectorySolution::fromDirectory($tempPath);

        $expectedBaseDir = SolutionPathTransformer::tempPathToSolutionTempPath($tempPath);

        $this->assertSame($expectedBaseDir, $solution->getBaseDirectory());
        $this->assertFalse($solution->hasComposerFile());
        $this->assertSame(sprintf('%s/solution.php', $expectedBaseDir), $solution->getEntryPoint());
        $files = $solution->getFiles();
        $this->assertCount(2, $files);

        $this->assertSame(sprintf('%s/solution.php', $expectedBaseDir), $files[0]->__toString());
        $this->assertSame(sprintf('%s/some-class.php', $expectedBaseDir), $files[1]->__toString());

        unlink(sprintf('%s/solution.php', $expectedBaseDir));
        unlink(sprintf('%s/some-class.php', $expectedBaseDir));
        unlink(sprintf('%s/solution.php', $tempPath));
        unlink(sprintf('%s/some-class.php', $tempPath));
        rmdir($tempPath);
        rmdir($expectedBaseDir);
    }

    public function testWithManualEntryPoint(): void
    {
        $tempPath = sprintf('%s/%s', realpath(sys_get_temp_dir()), $this->getName());
        @mkdir($tempPath, 0775, true);
        touch(sprintf('%s/index.php', $tempPath));
        touch(sprintf('%s/some-class.php', $tempPath));

        $solution = DirectorySolution::fromDirectory($tempPath, [], 'index.php');

        $expectedBaseDir = SolutionPathTransformer::tempPathToSolutionTempPath($tempPath);

        $this->assertSame($expectedBaseDir, $solution->getBaseDirectory());
        $this->assertFalse($solution->hasComposerFile());
        $this->assertSame(sprintf('%s/index.php', $expectedBaseDir), $solution->getEntryPoint());
        $files = $solution->getFiles();
        $this->assertCount(2, $files);

        $this->assertSame(sprintf('%s/index.php', $expectedBaseDir), $files[0]->__toString());
        $this->assertSame(sprintf('%s/some-class.php', $expectedBaseDir), $files[1]->__toString());

        unlink(sprintf('%s/index.php', $expectedBaseDir));
        unlink(sprintf('%s/some-class.php', $expectedBaseDir));
        unlink(sprintf('%s/index.php', $tempPath));
        unlink(sprintf('%s/some-class.php', $tempPath));
        rmdir($tempPath);
        rmdir($expectedBaseDir);
    }

    public function testHasComposerFileReturnsTrueIfPresent(): void
    {
        $tempPath = sprintf('%s/%s', realpath(sys_get_temp_dir()), $this->getName());
        @mkdir($tempPath, 0775, true);
        touch(sprintf('%s/solution.php', $tempPath));
        touch(sprintf('%s/some-class.php', $tempPath));
        touch(sprintf('%s/composer.lock', $tempPath));

        $solution = DirectorySolution::fromDirectory($tempPath);

        $expectedBaseDir = SolutionPathTransformer::tempPathToSolutionTempPath($tempPath);

        $this->assertSame($expectedBaseDir, $solution->getBaseDirectory());
        $this->assertTrue($solution->hasComposerFile());
        $this->assertSame(sprintf('%s/solution.php', $expectedBaseDir), $solution->getEntryPoint());
        $files = $solution->getFiles();
        $this->assertCount(2, $files);

        $this->assertSame(sprintf('%s/solution.php', $expectedBaseDir), $files[0]->__toString());
        $this->assertSame(sprintf('%s/some-class.php', $expectedBaseDir), $files[1]->__toString());

        unlink(sprintf('%s/composer.lock', $expectedBaseDir));
        unlink(sprintf('%s/solution.php', $expectedBaseDir));
        unlink(sprintf('%s/some-class.php', $expectedBaseDir));
        unlink(sprintf('%s/composer.lock', $tempPath));
        unlink(sprintf('%s/solution.php', $tempPath));
        unlink(sprintf('%s/some-class.php', $tempPath));
    }

    public function testWithExceptions(): void
    {
        $tempPath = sprintf('%s/%s', realpath(sys_get_temp_dir()), $this->getName());
        @mkdir($tempPath, 0775, true);
        touch(sprintf('%s/solution.php', $tempPath));
        touch(sprintf('%s/some-class.php', $tempPath));
        touch(sprintf('%s/exclude.txt', $tempPath));

        $exclusions = ['exclude.txt'];

        $solution = DirectorySolution::fromDirectory($tempPath, $exclusions);

        $expectedBaseDir = SolutionPathTransformer::tempPathToSolutionTempPath($tempPath);

        $this->assertSame(sprintf('%s/solution.php', $expectedBaseDir), $solution->getEntryPoint());
        $files = $solution->getFiles();
        $this->assertCount(2, $files);

        $this->assertSame(sprintf('%s/solution.php', $expectedBaseDir), $files[0]->__toString());
        $this->assertSame(sprintf('%s/some-class.php', $expectedBaseDir), $files[1]->__toString());

        unlink(sprintf('%s/solution.php', $expectedBaseDir));
        unlink(sprintf('%s/some-class.php', $expectedBaseDir));
        unlink(sprintf('%s/exclude.txt', $expectedBaseDir));
        unlink(sprintf('%s/solution.php', $tempPath));
        unlink(sprintf('%s/some-class.php', $tempPath));
        unlink(sprintf('%s/exclude.txt', $tempPath));
        rmdir($tempPath);
        rmdir($expectedBaseDir);
    }

    public function testWithNestedDirectories(): void
    {
        $tempPath = sprintf('%s/%s', realpath(sys_get_temp_dir()), $this->getName());
        @mkdir($tempPath, 0775, true);

        @mkdir(sprintf('%s/nested', $tempPath), 0775, true);
        @mkdir(sprintf('%s/nested/deep', $tempPath), 0775, true);

        touch(sprintf('%s/solution.php', $tempPath));
        touch(sprintf('%s/some-class.php', $tempPath));
        touch(sprintf('%s/composer.json', $tempPath));
        touch(sprintf('%s/nested/another-class.php', $tempPath));
        touch(sprintf('%s/nested/deep/even-more.php', $tempPath));

        $solution = DirectorySolution::fromDirectory($tempPath);

        $expectedBaseDir = SolutionPathTransformer::tempPathToSolutionTempPath($tempPath);

        $this->assertSame(sprintf('%s/solution.php', $expectedBaseDir), $solution->getEntryPoint());
        $files = $solution->getFiles();
        $this->assertCount(5, $files);

        $this->assertSame(sprintf('%s/composer.json', $expectedBaseDir), $files[0]->__toString());
        $this->assertSame(sprintf('%s/nested/another-class.php', $expectedBaseDir), $files[1]->__toString());
        $this->assertSame(sprintf('%s/nested/deep/even-more.php', $expectedBaseDir), $files[2]->__toString());
        $this->assertSame(sprintf('%s/solution.php', $expectedBaseDir), $files[3]->__toString());
        $this->assertSame(sprintf('%s/some-class.php', $expectedBaseDir), $files[4]->__toString());

        unlink(sprintf('%s/solution.php', $tempPath));
        unlink(sprintf('%s/some-class.php', $tempPath));
        unlink(sprintf('%s/composer.json', $tempPath));
        unlink(sprintf('%s/nested/another-class.php', $tempPath));
        unlink(sprintf('%s/nested/deep/even-more.php', $tempPath));
        rmdir(sprintf('%s/nested/deep', $tempPath));
        rmdir(sprintf('%s/nested', $tempPath));
        rmdir($tempPath);
        unlink(sprintf('%s/solution.php', $expectedBaseDir));
        unlink(sprintf('%s/some-class.php', $expectedBaseDir));
        unlink(sprintf('%s/composer.json', $expectedBaseDir));
        unlink(sprintf('%s/nested/another-class.php', $expectedBaseDir));
        unlink(sprintf('%s/nested/deep/even-more.php', $expectedBaseDir));
        rmdir(sprintf('%s/nested/deep', $expectedBaseDir));
        rmdir(sprintf('%s/nested', $expectedBaseDir));
        rmdir($expectedBaseDir);
    }

    public function testExceptionsWithNestedDirectories(): void
    {
        $tempPath = sprintf('%s/%s', realpath(sys_get_temp_dir()), $this->getName());
        @mkdir($tempPath, 0775, true);

        @mkdir(sprintf('%s/nested', $tempPath), 0775, true);
        @mkdir(sprintf('%s/nested/deep', $tempPath), 0775, true);
        @mkdir(sprintf('%s/vendor', $tempPath), 0775, true);
        @mkdir(sprintf('%s/vendor/somelib', $tempPath), 0775, true);

        touch(sprintf('%s/solution.php', $tempPath));
        touch(sprintf('%s/some-class.php', $tempPath));
        touch(sprintf('%s/exclude.txt', $tempPath));
        touch(sprintf('%s/nested/exclude.txt', $tempPath));
        touch(sprintf('%s/nested/deep/exclude.txt', $tempPath));
        touch(sprintf('%s/vendor/somelib/app.php', $tempPath));

        $exclusions = ['exclude.txt', 'vendor'];

        $solution = DirectorySolution::fromDirectory($tempPath, $exclusions);

        $expectedBaseDir = SolutionPathTransformer::tempPathToSolutionTempPath($tempPath);

        $this->assertSame(sprintf('%s/solution.php', $expectedBaseDir), $solution->getEntryPoint());
        $files = $solution->getFiles();
        $this->assertCount(2, $files);

        $this->assertSame(sprintf('%s/solution.php', $expectedBaseDir), $files[0]->__toString());
        $this->assertSame(sprintf('%s/some-class.php', $expectedBaseDir), $files[1]->__toString());

        unlink(sprintf('%s/solution.php', $tempPath));
        unlink(sprintf('%s/some-class.php', $tempPath));
        unlink(sprintf('%s/exclude.txt', $tempPath));
        unlink(sprintf('%s/nested/exclude.txt', $tempPath));
        unlink(sprintf('%s/nested/deep/exclude.txt', $tempPath));
        unlink(sprintf('%s/vendor/somelib/app.php', $tempPath));
        rmdir(sprintf('%s/nested/deep', $tempPath));
        rmdir(sprintf('%s/nested', $tempPath));
        rmdir(sprintf('%s/vendor/somelib', $tempPath));
        rmdir(sprintf('%s/vendor', $tempPath));
        rmdir($tempPath);
        unlink(sprintf('%s/solution.php', $expectedBaseDir));
        unlink(sprintf('%s/some-class.php', $expectedBaseDir));
        unlink(sprintf('%s/exclude.txt', $expectedBaseDir));
        unlink(sprintf('%s/nested/exclude.txt', $expectedBaseDir));
        unlink(sprintf('%s/nested/deep/exclude.txt', $expectedBaseDir));
        unlink(sprintf('%s/vendor/somelib/app.php', $expectedBaseDir));
        rmdir(sprintf('%s/nested/deep', $expectedBaseDir));
        rmdir(sprintf('%s/nested', $expectedBaseDir));
        rmdir(sprintf('%s/vendor/somelib', $expectedBaseDir));
        rmdir(sprintf('%s/vendor', $expectedBaseDir));
        rmdir($expectedBaseDir);
    }
}
