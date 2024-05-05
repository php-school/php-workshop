<?php

namespace PhpSchool\PhpWorkshopTest\ExerciseRunner\Context;

use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\ExerciseRunner\Context\ExecutionContextFactory;
use PhpSchool\PhpWorkshop\Input\Input;
use PHPUnit\Framework\TestCase;

class ExecutionContextFactoryTest extends TestCase
{
    public function testCanCreateContextFromInputAndExercise(): void
    {
        $exercise = $this->createMock(ExerciseInterface::class);
        $input = new Input('workshop', ['program' => '/path/to/program.php']);

        $factory = new ExecutionContextFactory();
        $context = $factory->fromInputAndExercise($input, $exercise);

        static::assertTrue($context->hasStudentSolution());
        static::assertSame('/path/to/program.php', $context->getEntryPoint());

        static::assertSame($context->studentExecutionDirectory, '/path/to');
        static::assertFileNotExists($context->referenceExecutionDirectory);
    }
}
