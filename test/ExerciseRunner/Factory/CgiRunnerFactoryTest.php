<?php

namespace PhpSchool\PhpWorkshopTest\ExerciseRunner\Factory;

use PhpSchool\PhpWorkshop\CommandDefinition;
use PhpSchool\PhpWorkshop\Event\EventDispatcher;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\Exercise\ExerciseType;
use PhpSchool\PhpWorkshop\ExerciseRunner\CgiRunner;
use PhpSchool\PhpWorkshop\ExerciseRunner\Factory\CgiRunnerFactory;
use PhpSchool\PhpWorkshop\Utils\RequestRenderer;
use PhpSchool\PhpWorkshopTest\Asset\CgiExerciseImpl;
use PHPUnit\Framework\TestCase;

class CgiRunnerFactoryTest extends TestCase
{
    /**
     * @var EventDispatcher
     */
    private $eventDispatcher;

    /**
     * @var CgiRunnerFactory
     */
    private $factory;

    public function setUp(): void
    {
        $this->eventDispatcher = $this->createMock(EventDispatcher::class);
        $this->factory = new CgiRunnerFactory($this->eventDispatcher, new RequestRenderer);
    }

    public function testSupports() : void
    {
        $exercise1 = $this->prophesize(ExerciseInterface::class);
        $exercise2 = $this->prophesize(ExerciseInterface::class);

        $exercise1->getType()->willReturn(ExerciseType::CGI());
        $exercise2->getType()->willReturn(ExerciseType::CLI());

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
        $exercise = new CgiExerciseImpl;
        $this->assertInstanceOf(CgiRunner::class, $this->factory->create($exercise));
    }
}
