<?php

namespace PhpSchool\PhpWorkshopTest\ExerciseRunner\Factory;

use PhpSchool\PhpWorkshop\CommandDefinition;
use PhpSchool\PhpWorkshop\Event\EventDispatcher;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\Exercise\ExerciseType;
use PhpSchool\PhpWorkshop\ExerciseRunner\CgiRunner;
use PhpSchool\PhpWorkshop\ExerciseRunner\EnvironmentManager;
use PhpSchool\PhpWorkshop\ExerciseRunner\Factory\CgiRunnerFactory;
use PhpSchool\PhpWorkshop\Process\HostProcessFactory;
use PhpSchool\PhpWorkshopTest\Asset\CgiExerciseImpl;
use PHPUnit\Framework\TestCase;

class CgiRunnerFactoryTest extends TestCase
{
    private EventDispatcher $eventDispatcher;
    private CgiRunnerFactory $factory;

    public function setUp(): void
    {
        $this->eventDispatcher = $this->createMock(EventDispatcher::class);
        $this->factory = new CgiRunnerFactory($this->eventDispatcher, new HostProcessFactory(), $this->createMock(EnvironmentManager::class));
    }

    public function testSupports(): void
    {
        $exercise1 = $this->createMock(ExerciseInterface::class);
        $exercise2 = $this->createMock(ExerciseInterface::class);

        $exercise1->method('getType')->willReturn(ExerciseType::CGI());
        $exercise2->method('getType')->willReturn(ExerciseType::CLI());

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
        $exercise = new CgiExerciseImpl();
        $this->assertInstanceOf(CgiRunner::class, $this->factory->create($exercise));
    }
}
