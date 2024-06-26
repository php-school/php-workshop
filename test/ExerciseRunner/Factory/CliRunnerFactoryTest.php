<?php

namespace PhpSchool\PhpWorkshopTest\ExerciseRunner\Factory;

use PhpSchool\PhpWorkshop\CommandDefinition;
use PhpSchool\PhpWorkshop\Event\EventDispatcher;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\Exercise\ExerciseType;
use PhpSchool\PhpWorkshop\ExerciseRunner\CliRunner;
use PhpSchool\PhpWorkshop\ExerciseRunner\EnvironmentManager;
use PhpSchool\PhpWorkshop\ExerciseRunner\Factory\CliRunnerFactory;
use PhpSchool\PhpWorkshop\Process\HostProcessFactory;
use PhpSchool\PhpWorkshopTest\Asset\CliExerciseImpl;
use PHPUnit\Framework\TestCase;

class CliRunnerFactoryTest extends TestCase
{
    private EventDispatcher $eventDispatcher;
    private CliRunnerFactory $factory;

    public function setUp(): void
    {
        $this->eventDispatcher = $this->createMock(EventDispatcher::class);
        $this->factory = new CliRunnerFactory($this->eventDispatcher, new HostProcessFactory(), $this->createMock(EnvironmentManager::class));
    }

    public function testSupports(): void
    {
        $exercise1 = $this->createMock(ExerciseInterface::class);
        $exercise2 = $this->createMock(ExerciseInterface::class);

        $exercise1->method('getType')->willReturn(ExerciseType::CLI());
        $exercise2->method('getType')->willReturn(ExerciseType::CGI());

        $this->assertTrue($this->factory->supports($exercise1));
        $this->assertFalse($this->factory->supports($exercise2));
    }

    public function testConfigureInputAddsProgramArgument(): void
    {
        $command = new CommandDefinition('my-command', [], 'var_dump');

        $this->factory->configureInput($command);

        $this->assertCount(1, $command->getRequiredArgs());
        $this->assertSame('program', $command->getRequiredArgs()[0]->getName());
        $this->assertTrue($command->getRequiredArgs()[0]->isRequired());
    }

    public function testCreateReturnsRunner(): void
    {
        $exercise = new CliExerciseImpl();
        $this->assertInstanceOf(CliRunner::class, $this->factory->create($exercise));
    }
}
