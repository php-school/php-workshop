<?php

namespace PhpSchool\PhpWorkshopTest\ExerciseRunner\Context;

use PhpSchool\PhpWorkshop\Exercise\MockExercise;
use PhpSchool\PhpWorkshop\ExerciseRunner\Context\TestContext;
use PHPUnit\Framework\TestCase;

class TestContextTest extends TestCase
{
    public function testThatDirectoriesAreNotCreatedInitially(): void
    {
        $context = new TestContext();

        static::assertFileNotExists($context->getStudentExecutionDirectory());
        static::assertFileNotExists($context->getReferenceExecutionDirectory());
    }

    public function testCreateDirectories(): void
    {
        $context = new TestContext();
        $context->createStudentSolutionDirectory();
        $context->createReferenceSolutionDirectory();

        static::assertFileExists($context->getStudentExecutionDirectory());
        static::assertFileExists($context->getReferenceExecutionDirectory());
    }

    public function testFromExerciseAndSolutionFactory(): void
    {
        $exercise = new MockExercise();
        $context = TestContext::fromExerciseAndStudentSolution($exercise, __FILE__);

        static::assertSame(__DIR__, $context->getStudentExecutionDirectory());
        static::assertSame(__FILE__, $context->getInput()->getRequiredArgument('program'));
        static::assertSame($exercise, $context->getExercise());
    }

    public function testImportStudentSolutionFileFromStringThrowsExceptionIfExecutionDirectoryDoesNotExist(): void
    {
        $this->expectException(\RuntimeException::class);

        $context = new TestContext();
        $context->importStudentFileFromString('<?php echo "yo";');
    }

    public function testImportStudentSolutionFileFromStringCreatesFileInExecutionDirectory(): void
    {
        $context = new TestContext();
        $context->createStudentSolutionDirectory();
        $context->importStudentFileFromString('<?php echo "yo";');

        static::assertFileExists($context->getStudentExecutionDirectory() . '/solution.php');
        static::assertEquals('<?php echo "yo";', file_get_contents($context->getStudentExecutionDirectory() . '/solution.php'));
    }

    public function testImportStudentSolutionFileFromStringWithCustomPathCreatesFileInExecutionDirectory(): void
    {
        $context = new TestContext();
        $context->createStudentSolutionDirectory();
        $context->importStudentFileFromString('<?php echo "yo";', 'some-file.php');

        static::assertFileExists($context->getStudentExecutionDirectory() . '/some-file.php');
        static::assertEquals('<?php echo "yo";', file_get_contents($context->getStudentExecutionDirectory() . '/some-file.php'));
    }

    public function testImportReferenceSolutionFileFromStringThrowsExceptionIfExecutionDirectoryDoesNotExist(): void
    {
        $this->expectException(\RuntimeException::class);

        $context = new TestContext();
        $context->importReferenceFileFromString('<?php echo "yo";');
    }

    public function testImportReferenceSolutionFileFromStringCreatesFileInExecutionDirectory(): void
    {
        $context = new TestContext();
        $context->createReferenceSolutionDirectory();
        $context->importReferenceFileFromString('<?php echo "yo";');

        static::assertFileExists($context->getReferenceExecutionDirectory() . '/solution.php');
        static::assertEquals('<?php echo "yo";', file_get_contents($context->getReferenceExecutionDirectory() . '/solution.php'));
    }

    public function testImportReferenceSolutionFileFromStringWithCustomPathCreatesFileInExecutionDirectory(): void
    {
        $context = new TestContext();
        $context->createReferenceSolutionDirectory();
        $context->importReferenceFileFromString('<?php echo "yo";', 'some-file.php');

        static::assertFileExists($context->getReferenceExecutionDirectory() . '/some-file.php');
        static::assertEquals('<?php echo "yo";', file_get_contents($context->getReferenceExecutionDirectory() . '/some-file.php'));
    }

    public function testDestructCleansUpExecutionDirectories(): void
    {
        $context = new TestContext();
        $context->createStudentSolutionDirectory();
        $context->createReferenceSolutionDirectory();

        $studentExecutionDirectory = $context->getStudentExecutionDirectory();
        $referenceExecutionDirectory = $context->getReferenceExecutionDirectory();

        static::assertFileExists($studentExecutionDirectory);
        static::assertFileExists($referenceExecutionDirectory);

        unset($context);

        static::assertFileNotExists($studentExecutionDirectory);
        static::assertFileNotExists($referenceExecutionDirectory);
    }
}
