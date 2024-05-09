<?php

namespace PhpSchool\PhpWorkshopTest\ExerciseRunner\Context;

use PhpSchool\PhpWorkshop\Exercise\TemporaryDirectoryTrait;
use PhpSchool\PhpWorkshop\ExerciseRunner\Context\TestContext;
use PhpSchool\PhpWorkshop\Solution\DirectorySolution;
use PhpSchool\PhpWorkshop\Utils\System;
use PHPUnit\Framework\TestCase;

class TestContextTest extends TestCase
{
//    public function testThatWithoutDirectoriesDoesNotCreateExecutionDirectories(): void
//    {
//        $context = TestContext::withoutFs();
//
//        static::assertFileNotExists($context->getStudentExecutionDirectory());
//        static::assertFileNotExists($context->getReferenceExecutionDirectory());
//    }
//
//    public function testImportStudentSolutionFileFromStringThrowsExceptionIfExecutionDirectoryDoesNotExist(): void
//    {
//        $this->expectException(\RuntimeException::class);
//
//        $context = TestContext::withoutFs();
//        $context->importStudentFileFromString('<?php echo "yo";');
//    }
//
//    public function testImportStudentSolutionFileFromStringCreatesFileInExecutionDirectory(): void
//    {
//        $context = new TestContext();
//        $context->createStudentSolutionDirectory();
//        $context->importStudentFileFromString('<?php echo "yo";');
//
//        static::assertFileExists($context->getStudentExecutionDirectory() . '/solution.php');
//        static::assertEquals('<?php echo "yo";', file_get_contents($context->getStudentExecutionDirectory() . '/solution.php'));
//    }
//
//    public function testImportStudentSolutionFileFromStringWithCustomPathCreatesFileInExecutionDirectory(): void
//    {
//        $context = new TestContext();
//        $context->createStudentSolutionDirectory();
//        $context->importStudentFileFromString('<?php echo "yo";', 'some-file.php');
//
//        static::assertFileExists($context->getStudentExecutionDirectory() . '/some-file.php');
//        static::assertEquals('<?php echo "yo";', file_get_contents($context->getStudentExecutionDirectory() . '/some-file.php'));
//    }
//
//    public function testImportReferenceSolutionFileFromStringThrowsExceptionIfExecutionDirectoryDoesNotExist(): void
//    {
//        $this->expectException(\RuntimeException::class);
//
//        $context = TestContext::withoutFs();
//        $context->importReferenceFileFromString('<?php echo "yo";');
//    }
//
//    public function testImportReferenceSolutionFileFromStringCreatesFileInExecutionDirectory(): void
//    {
//        $context = new TestContext();
//        $context->createReferenceSolutionDirectory();
//        $context->importReferenceFileFromString('<?php echo "yo";');
//
//        static::assertFileExists($context->getReferenceExecutionDirectory() . '/solution.php');
//        static::assertEquals('<?php echo "yo";', file_get_contents($context->getReferenceExecutionDirectory() . '/solution.php'));
//    }
//
//    public function testImportReferenceSolutionFileFromStringWithCustomPathCreatesFileInExecutionDirectory(): void
//    {
//        $context = new TestContext();
//        $context->createReferenceSolutionDirectory();
//        $context->importReferenceFileFromString('<?php echo "yo";', 'some-file.php');
//
//        static::assertFileExists($context->getReferenceExecutionDirectory() . '/some-file.php');
//        static::assertEquals('<?php echo "yo";', file_get_contents($context->getReferenceExecutionDirectory() . '/some-file.php'));
//    }
//
//    public function testImportReferenceSolutionFolderThrowsExceptionIfExecutionDirectoryDoesNotExist(): void
//    {
//        $this->expectException(\RuntimeException::class);
//
//        $context = TestContext::withoutDirectories();
//        $context->importReferenceSolution($this->createMock(DirectorySolution::class));
//    }
//
//    public function testImportReferenceSolutionFolderCopiesSolutionToExecutionDirectory(): void
//    {
//        $context = TestContext::withDirectories();
//
//        $context->importReferenceSolution(DirectorySolution::fromDirectory(__DIR__, [], basename(__FILE__)));
//
//        static::assertFileExists($context->getStudentExecutionDirectory());
//        static::assertCount($this->getFileCountInThisDirectory(), scandir($context->getReferenceExecutionDirectory()));
//    }
//
//    public function testDestructCleansUpExecutionDirectories(): void
//    {
//        $context = TestContext::withDirectories();
//
//        $studentExecutionDirectory = $context->getStudentExecutionDirectory();
//        $referenceExecutionDirectory = $context->getReferenceExecutionDirectory();
//
//        static::assertFileExists($studentExecutionDirectory);
//        static::assertFileExists($referenceExecutionDirectory);
//
//        unset($context);
//
//        static::assertFileNotExists($studentExecutionDirectory);
//        static::assertFileNotExists($referenceExecutionDirectory);
//    }
//
//    public function testDestructCleansUpReferenceExecutionDirectories(): void
//    {
//        $context = new TestContext();
//
//        $referenceExecutionDirectory = $context->getReferenceExecutionDirectory();
//
//        static::assertFileExists($referenceExecutionDirectory);
//
//        unset($context);
//
//        static::assertFileNotExists($referenceExecutionDirectory);
//    }


    private function getFileCountInThisDirectory(): int
    {
        return count(scandir(__DIR__));
    }
}
