<?php

namespace PhpSchool\PhpWorkshopTest\ExerciseRunner\Context;

use PhpSchool\PhpWorkshop\Exercise\MockExercise;
use PhpSchool\PhpWorkshop\ExerciseRunner\Context\ExecutionContextFactory;
use PhpSchool\PhpWorkshop\Input\Input;
use PhpSchool\PhpWorkshop\Utils\System;
use PHPUnit\Framework\TestCase;

class ExecutionContextFactoryTest extends TestCase
{
    public function testFactory(): void
    {
        $factory = new ExecutionContextFactory();

        $temporaryDirectory = System::randomTempDir();

        $input = new Input('test', ['program' => $temporaryDirectory . '/solution.php']);
        $exercise = new MockExercise();

        $context = $factory->fromInputAndExercise($input, $exercise);

        //check that student execution directory uses the parent directory of the program from the input
        static::assertSame($temporaryDirectory, $context->getStudentExecutionDirectory());
        static::assertSame($temporaryDirectory . '/solution.php', $context->getEntryPoint());

        //check that reference execution directory is a random temporary directory
        static::assertTrue(str_starts_with($context->getReferenceExecutionDirectory(), System::tempDir()));
    }
}
