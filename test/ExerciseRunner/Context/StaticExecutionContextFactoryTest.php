<?php

namespace PhpSchool\PhpWorkshopTest\ExerciseRunner\Context;

use PhpSchool\PhpWorkshop\Exercise\MockExercise;
use PhpSchool\PhpWorkshop\ExerciseRunner\Context\StaticExecutionContextFactory;
use PhpSchool\PhpWorkshop\ExerciseRunner\Context\TestContext;
use PhpSchool\PhpWorkshop\Input\Input;
use PHPUnit\Framework\TestCase;

class StaticExecutionContextFactoryTest extends TestCase
{
    public function testFactoryReturnsGivenTestContext(): void
    {
        $context = TestContext::withoutDirectories(
            new Input('test', ['program' => 'solution.php']),
            new MockExercise()
        );

        $factory = new StaticExecutionContextFactory($context);

        static::assertSame($context, $factory->fromInputAndExercise(new Input('test', []), new MockExercise()));
    }
}
