<?php

namespace PhpSchool\PhpWorkshopTest\ExerciseRunner\Context;

use PhpSchool\PhpWorkshop\Exercise\MockExercise;
use PhpSchool\PhpWorkshop\ExerciseRunner\Context\ExecutionContext;
use PhpSchool\PhpWorkshop\ExerciseRunner\Context\NoEntryPoint;
use PhpSchool\PhpWorkshop\Input\Input;
use PhpSchool\PhpWorkshop\Utils\System;
use PHPUnit\Framework\TestCase;

class ExecutionContextTest extends TestCase
{
    public function testGetters(): void
    {
        $exercise = new MockExercise();
        $input = new Input('test', ['program' => 'solution.php']);
        $context = new ExecutionContext(
            '/student-dir',
            '/reference-dir',
            $exercise,
            $input
        );

        static::assertSame($exercise, $context->getExercise());
        static::assertSame($input, $context->getInput());
        static::assertSame('/student-dir', $context->getStudentExecutionDirectory());
        static::assertSame('/reference-dir', $context->getReferenceExecutionDirectory());
    }

    public function testHasStudentSolution(): void
    {
        $exercise = new MockExercise();
        $input = new Input('test', ['program' => 'solution.php']);
        $context = new ExecutionContext(
            '/student-dir',
            '/reference-dir',
            $exercise,
            $input
        );

        static::assertTrue($context->hasStudentSolution());

        $exercise = new MockExercise();
        $input = new Input('test');
        $context = new ExecutionContext(
            '/student-dir',
            '/reference-dir',
            $exercise,
            $input
        );

        static::assertFalse($context->hasStudentSolution());
    }

    public function testGetEntryPoint(): void
    {
        $exercise = new MockExercise();
        $input = new Input('test', ['program' => 'solution.php']);
        $context = new ExecutionContext(
            '/student-dir',
            '/reference-dir',
            $exercise,
            $input
        );

        static::assertSame('/student-dir/solution.php', $context->getEntryPoint());
    }

    public function testGetEntryPointThrowsExceptionWhenNoStudentSolution(): void
    {
        static::expectException(NoEntryPoint::class);

        $exercise = new MockExercise();
        $input = new Input('test');
        $context = new ExecutionContext(
            '/student-dir',
            '/reference-dir',
            $exercise,
            $input
        );

        $context->getEntryPoint();
    }

    public function testFactory(): void
    {
        $temporaryDirectory = System::randomTempDir();

        $input = new Input('test', ['program' => $temporaryDirectory . '/solution.php']);
        $exercise = new MockExercise();

        $context = ExecutionContext::fromInputAndExercise($input, $exercise);

        //check that student execution directory uses the parent directory of the program from the input
        static::assertSame($temporaryDirectory, $context->getStudentExecutionDirectory());
        static::assertSame($temporaryDirectory . '/solution.php', $context->getEntryPoint());

        //check that reference execution directory is a random temporary directory
        static::assertTrue(str_starts_with($context->getReferenceExecutionDirectory(), System::tempDir()));
    }
}
