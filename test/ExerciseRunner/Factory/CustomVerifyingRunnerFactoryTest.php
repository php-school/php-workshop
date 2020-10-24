<?php

namespace PhpSchool\PhpWorkshopTest\ExerciseRunner\Factory;

use PhpSchool\PhpWorkshop\CommandDefinition;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\Exercise\ExerciseType;
use PhpSchool\PhpWorkshop\ExerciseRunner\CustomVerifyingRunner;
use PhpSchool\PhpWorkshop\ExerciseRunner\Factory\CustomVerifyingRunnerFactory;
use PhpSchool\PhpWorkshopTest\Asset\CustomVerifyingExerciseImpl;
use PHPUnit\Framework\TestCase;

class CustomVerifyingRunnerFactoryTest extends TestCase
{
    /**
     * @var CustomVerifyingRunnerFactory
     */
    private $factory;

    public function setUp(): void
    {
        $this->factory = new CustomVerifyingRunnerFactory();
    }

    public function testSupports(): void
    {
        $exercise1 = $this->createMock(ExerciseInterface::class);
        $exercise2 = $this->createMock(ExerciseInterface::class);
        $exercise3 = $this->createMock(ExerciseInterface::class);

        $exercise1->method('getType')->willReturn(ExerciseType::CLI());
        $exercise2->method('getType')->willReturn(ExerciseType::CGI());
        $exercise3->method('getType')->willReturn(ExerciseType::CUSTOM());

        $this->assertFalse($this->factory->supports($exercise1));
        $this->assertFalse($this->factory->supports($exercise2));
        $this->assertTrue($this->factory->supports($exercise3));
    }

    public function testConfigureInputAddsNoArgument(): void
    {
        $command = new CommandDefinition('my-command', [], 'var_dump');

        $this->factory->configureInput($command);
        $this->assertCount(0, $command->getRequiredArgs());
    }

    public function testCreateReturnsRunner(): void
    {
        $exercise = new CustomVerifyingExerciseImpl();
        $this->assertInstanceOf(CustomVerifyingRunner::class, $this->factory->create($exercise));
    }
}
