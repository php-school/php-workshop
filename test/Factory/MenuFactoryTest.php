<?php

namespace PhpSchool\PhpWorkshopTest\Factory;

use PhpSchool\PhpWorkshop\Event\EventInterface;
use PhpSchool\PhpWorkshop\UserState\Serializer;
use PhpSchool\PhpWorkshop\UserState\UserState;
use Psr\Container\ContainerInterface;
use PhpSchool\CliMenu\CliMenu;
use PhpSchool\PhpWorkshop\Command\CreditsCommand;
use PhpSchool\PhpWorkshop\Command\HelpCommand;
use PhpSchool\PhpWorkshop\Event\EventDispatcher;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\ExerciseRenderer;
use PhpSchool\PhpWorkshop\ExerciseRepository;
use PhpSchool\PhpWorkshop\Factory\MenuFactory;
use PhpSchool\PhpWorkshop\MenuItem\ResetProgress;
use PhpSchool\PhpWorkshop\WorkshopType;
use PhpSchool\Terminal\Terminal;
use PHPUnit\Framework\TestCase;

class MenuFactoryTest extends TestCase
{
    public function testFactoryReturnsInstance(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $userStateSerializer = $this->createMock(Serializer::class);
        $userStateSerializer
            ->expects($this->once())
            ->method('deSerialize')
            ->willReturn(new UserState());

        $exerciseRepository = $this->createMock(ExerciseRepository::class);
        $exercise = $this->createMock(ExerciseInterface::class);
        $exercise->expects($this->exactly(2))
            ->method('getName')
            ->willReturn('Exercise');
        $exerciseRepository
            ->expects($this->once())
            ->method('findAll')
            ->willReturn([$exercise]);

        $terminal = $this->createMock(Terminal::class);
        $terminal
            ->method('getWidth')
            ->willReturn(70);

        $services = [
            Serializer::class => $userStateSerializer,
            ExerciseRepository::class => $exerciseRepository,
            ExerciseRenderer::class => $this->createMock(ExerciseRenderer::class),
            HelpCommand::class => $this->createMock(HelpCommand::class),
            CreditsCommand::class => $this->createMock(CreditsCommand::class),
            ResetProgress::class => $this->createMock(ResetProgress::class),
            'workshopLogo'  => 'LOGO',
            'bgColour'      => 'black',
            'fgColour'      => 'green',
            'workshopTitle' => 'TITLE',
            WorkshopType::class => WorkshopType::STANDARD(),
            EventDispatcher::class => $this->createMock(EventDispatcher::class),
            Terminal::class => $terminal,
        ];

        $container
            ->method('get')
            ->willReturnCallback(function ($name) use ($services) {
                return $services[$name];
            });


        $factory = new MenuFactory();

        $factory($container);
    }

    public function testSelectExercise(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $userStateSerializer = $this->createMock(Serializer::class);
        $userStateSerializer
            ->expects($this->once())
            ->method('deSerialize')
            ->willReturn(new UserState());

        $exerciseRepository = $this->createMock(ExerciseRepository::class);
        $exercise = $this->createMock(ExerciseInterface::class);
        $exercise
            ->method('getName')
            ->willReturn('Exercise');
        $exerciseRepository
            ->expects($this->once())
            ->method('findAll')
            ->willReturn([$exercise]);

        $terminal = $this->createMock(Terminal::class);
        $terminal
            ->method('getWidth')
            ->willReturn(70);

        $eventDispatcher = $this->createMock(EventDispatcher::class);
        $eventDispatcher
            ->expects(self::exactly(2))
            ->method('dispatch')
            ->withConsecutive(
                [
                    self::callback(function ($event) {
                        return $event instanceof EventInterface && $event->getName() === 'exercise.selected';
                    }),
                ],
                [
                    self::callback(function ($event) {
                        return $event instanceof EventInterface && $event->getName() === 'exercise.selected.exercise';
                    }),
                ],
            );

        $exerciseRenderer = $this->createMock(ExerciseRenderer::class);
        $exerciseRenderer->expects(self::once())
            ->method('__invoke')
            ->with(self::isInstanceOf(CliMenu::class));

        $services = [
            Serializer::class => $userStateSerializer,
            ExerciseRepository::class => $exerciseRepository,
            ExerciseRenderer::class => $exerciseRenderer,
            HelpCommand::class => $this->createMock(HelpCommand::class),
            CreditsCommand::class => $this->createMock(CreditsCommand::class),
            ResetProgress::class => $this->createMock(ResetProgress::class),
            'workshopLogo'  => 'LOGO',
            'bgColour'      => 'black',
            'fgColour'      => 'green',
            'workshopTitle' => 'TITLE',
            WorkshopType::class => WorkshopType::STANDARD(),
            EventDispatcher::class => $eventDispatcher,
            Terminal::class => $terminal,
        ];

        $container
            ->method('get')
            ->willReturnCallback(function ($name) use ($services) {
                return $services[$name];
            });


        $factory = new MenuFactory();

        $menu = $factory($container);

        $firstExercise = $menu->getItemByIndex(6);
        $menu->executeAsSelected($firstExercise);
    }
}
