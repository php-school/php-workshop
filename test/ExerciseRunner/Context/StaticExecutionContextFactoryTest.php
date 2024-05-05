<?php

namespace PhpSchool\PhpWorkshopTest\ExerciseRunner\Context;

use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\ExerciseRunner\Context\StaticExecutionContextFactory;
use PhpSchool\PhpWorkshop\ExerciseRunner\Context\TestContext;
use PhpSchool\PhpWorkshop\Input\Input;
use PHPUnit\Framework\TestCase;

class StaticExecutionContextFactoryTest extends TestCase
{
    public function testStaticExecutionContextFactory(): void
    {
        $exercise = $this->createMock(ExerciseInterface::class);
        $input = new Input('workshop', ['program' => '/path/to/program.php']);

        $context = TestContext::withoutEnvironment();
        $factory = new StaticExecutionContextFactory($context);

        static::assertSame($context, $factory->fromInputAndExercise($input, $exercise));
    }
}
