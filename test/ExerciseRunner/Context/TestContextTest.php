<?php

namespace PhpSchool\PhpWorkshopTest\ExerciseRunner\Context;

use PhpSchool\PhpWorkshop\Exercise\TemporaryDirectoryTrait;
use PhpSchool\PhpWorkshop\ExerciseRunner\Context\TestContext;
use PhpSchool\PhpWorkshop\Solution\DirectorySolution;
use PhpSchool\PhpWorkshop\Utils\System;
use PHPUnit\Framework\TestCase;

class TestContextTest extends TestCase
{
    public function testThatWithoutDirectoriesDoesNotCreateExecutionDirectories(): void
    {
        $context = TestContext::withoutDirectories();

        static::assertFileNotExists($context->getStudentExecutionDirectory());
        static::assertFileNotExists($context->getReferenceExecutionDirectory());
    }

    public function testWithDirectoriesCreatesExecutionDirectories(): void
    {
        $context = TestContext::withDirectories();

        static::assertFileExists($context->getStudentExecutionDirectory());
        static::assertFileExists($context->getReferenceExecutionDirectory());
    }

    public function testImportStudentSolutionFileFromStingThrowsExceptionIfExecutionDirectoryDoesNotExist(): void
    {
        $this->expectException(\RuntimeException::class);

        $context = TestContext::withoutDirectories();
        $context->importStudentFileFromString('<?php echo "yo";');
    }

    public function testImportStudentSolutionFileFromStingCreatesFileInExecutionDirectory(): void
    {
        $context = TestContext::withDirectories();

        $context->importStudentFileFromString('<?php echo "yo";');

        static::assertFileExists($context->getStudentExecutionDirectory() . '/solution.php');
        static::assertEquals('<?php echo "yo";', file_get_contents($context->getStudentExecutionDirectory() . '/solution.php'));
    }

    public function testImportStudentSolutionFileFromStingWithCustomPathCreatesFileInExecutionDirectory(): void
    {
        $context = TestContext::withDirectories();

        $context->importStudentFileFromString('<?php echo "yo";', 'some-file.php');

        static::assertFileExists($context->getStudentExecutionDirectory() . '/some-file.php');
        static::assertEquals('<?php echo "yo";', file_get_contents($context->getStudentExecutionDirectory() . '/some-file.php'));
    }

    public function testImportStudentSolutionThrowsExceptionIfExecutionDirectoryDoesNotExist(): void
    {
        $this->expectException(\RuntimeException::class);

        $context = TestContext::withoutDirectories();
        $context->importStudentSolution('path/to/solution.php');
    }

    public function testImportStudentSolutionCopiesSolutionToExecutionDirectory(): void
    {
        $context = TestContext::withDirectories();

        $context->importStudentSolution(__FILE__);

        static::assertFileExists($context->getStudentExecutionDirectory() . '/solution.php');
        static::assertFileEquals(__FILE__, $context->getStudentExecutionDirectory() . '/solution.php');
    }

    public function testImportStudentSolutionFolderThrowsExceptionIfExecutionDirectoryDoesNotExist(): void
    {
        $this->expectException(\RuntimeException::class);

        $context = TestContext::withoutDirectories();
        $context->importStudentSolutionFolder('path/to/solution');
    }

    public function testImportStudentSolutionFolderCopiesSolutionToExecutionDirectory(): void
    {
        $context = TestContext::withDirectories();

        $context->importStudentSolutionFolder(__DIR__);

        static::assertFileExists($context->getStudentExecutionDirectory());
        static::assertCount($this->getFileCountInThisDirectory(), scandir($context->getStudentExecutionDirectory()));
    }

    public function testImportReferenceSolutionFileFromStingThrowsExceptionIfExecutionDirectoryDoesNotExist(): void
    {
        $this->expectException(\RuntimeException::class);

        $context = TestContext::withoutDirectories();
        $context->importReferenceFileFromString('<?php echo "yo";');
    }

    public function testImportReferenceSolutionFileFromStingCreatesFileInExecutionDirectory(): void
    {
        $context = TestContext::withDirectories();

        $context->importReferenceFileFromString('<?php echo "yo";');

        static::assertFileExists($context->getReferenceExecutionDirectory() . '/solution.php');
        static::assertEquals('<?php echo "yo";', file_get_contents($context->getReferenceExecutionDirectory() . '/solution.php'));
    }

    public function testImportReferenceSolutionFileFromStingWithCustomPathCreatesFileInExecutionDirectory(): void
    {
        $context = TestContext::withDirectories();

        $context->importReferenceFileFromString('<?php echo "yo";', 'some-file.php');

        static::assertFileExists($context->getReferenceExecutionDirectory() . '/some-file.php');
        static::assertEquals('<?php echo "yo";', file_get_contents($context->getReferenceExecutionDirectory() . '/some-file.php'));
    }


    public function testImportReferenceSolutionFolderThrowsExceptionIfExecutionDirectoryDoesNotExist(): void
    {
        $this->expectException(\RuntimeException::class);

        $context = TestContext::withoutDirectories();
        $context->importReferenceSolution($this->createMock(DirectorySolution::class));
    }

    public function testImportReferenceSolutionFolderCopiesSolutionToExecutionDirectory(): void
    {
        $context = TestContext::withDirectories();

        $context->importReferenceSolution(DirectorySolution::fromDirectory(__DIR__, [], basename(__FILE__)));

        static::assertFileExists($context->getStudentExecutionDirectory());
        static::assertCount($this->getFileCountInThisDirectory(), scandir($context->getReferenceExecutionDirectory()));
    }

    public function testDestructCleansUpExecutionDirectories(): void
    {
        $context = TestContext::withDirectories();

        $studentExecutionDirectory = $context->getStudentExecutionDirectory();
        $referenceExecutionDirectory = $context->getReferenceExecutionDirectory();

        static::assertFileExists($studentExecutionDirectory);
        static::assertFileExists($referenceExecutionDirectory);

        unset($context);

        static::assertFileNotExists($studentExecutionDirectory);
        static::assertFileNotExists($referenceExecutionDirectory);
    }

    private function getFileCountInThisDirectory(): int
    {
        return count(scandir(__DIR__));
    }
}
