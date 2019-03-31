<?php

namespace PhpSchool\PhpWorkshopTest\ExerciseRunner\Factory;

use PhpSchool\PhpWorkshop\CommandDefinition;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\Exercise\ExerciseType;
use PhpSchool\PhpWorkshop\ExerciseRunner\CustomVerifyingRunner;
use PhpSchool\PhpWorkshop\ExerciseRunner\Factory\CustomVerifyingRunnerFactory;
use PhpSchool\PhpWorkshopTest\Asset\CustomVerifyingExerciseImpl;
use PHPUnit\Framework\TestCase;

/**
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class CustomRunnerFactoryTest extends TestCase
{
    /**
     * @var CustomVerifyingRunnerFactory
     */
    private $factory;

    public function setUp()
    {
        $this->factory = new CustomVerifyingRunnerFactory;
    }

    public function testSupports()
    {
        $exercise1 = $this->prophesize(ExerciseInterface::class);
        $exercise2 = $this->prophesize(ExerciseInterface::class);
        $exercise3 = $this->prophesize(ExerciseInterface::class);

        $exercise1->getType()->willReturn(ExerciseType::CLI());
        $exercise2->getType()->willReturn(ExerciseType::CGI());
        $exercise3->getType()->willReturn(ExerciseType::CUSTOM());

        $this->assertFalse($this->factory->supports($exercise1->reveal()));
        $this->assertFalse($this->factory->supports($exercise2->reveal()));
        $this->assertTrue($this->factory->supports($exercise3->reveal()));
    }

    public function testConfigureInputAddsNoArgument()
    {
        $command = new CommandDefinition('my-command', [], 'var_dump');

        $this->factory->configureInput($command);
        $this->assertCount(0, $command->getRequiredArgs());
    }

    public function testCreateReturnsRunner()
    {
        $exercise = new CustomVerifyingExerciseImpl;
        $this->assertInstanceOf(CustomVerifyingRunner::class, $this->factory->create($exercise));
    }
}
