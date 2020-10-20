<?php

namespace PhpSchool\PhpWorkshopTest\ExerciseRunner\Factory;

use PhpSchool\PhpWorkshop\CommandDefinition;
use PhpSchool\PhpWorkshop\Event\EventDispatcher;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\Exercise\ExerciseType;
use PhpSchool\PhpWorkshop\ExerciseRunner\CliRunner;
use PhpSchool\PhpWorkshop\ExerciseRunner\Factory\CliRunnerFactory;
use PhpSchool\PhpWorkshopTest\Asset\CliExerciseImpl;
use PHPUnit\Framework\TestCase;

class CliRunnerFactoryTest extends TestCase
{
    /**
     * @var EventDispatcher
     */
    private $eventDispatcher;

    /**
     * @var CliRunnerFactory
     */
    private $factory;

    public function setUp(): void
    {
        $this->eventDispatcher = $this->createMock(EventDispatcher::class);
        $this->factory = new CliRunnerFactory($this->eventDispatcher);
    }

    public function testSupports() : void
    {
        $exercise1 = $this->prophesize(ExerciseInterface::class);
        $exercise2 = $this->prophesize(ExerciseInterface::class);

        $exercise1->getType()->willReturn(ExerciseType::CLI());
        $exercise2->getType()->willReturn(ExerciseType::CGI());

        $this->assertTrue($this->factory->supports($exercise1->reveal()));
        $this->assertFalse($this->factory->supports($exercise2->reveal()));
    }

    public function testConfigureInputAddsProgramArgument() : void
    {
        $command = new CommandDefinition('my-command', [], 'var_dump');

        $this->factory->configureInput($command);

        $this->assertCount(1, $command->getRequiredArgs());
        $this->assertSame('program', $command->getRequiredArgs()[0]->getName());
        $this->assertTrue($command->getRequiredArgs()[0]->isRequired());
    }

    public function testCreateReturnsRunner() : void
    {
        $exercise = new CliExerciseImpl;
        $this->assertInstanceOf(CliRunner::class, $this->factory->create($exercise));
    }
}
