<?php

namespace PhpSchool\PhpWorkshopTest\Factory;

use Interop\Container\ContainerInterface;
use PhpSchool\CliMenu\CliMenu;
use PhpSchool\PhpWorkshop\Command\CreditsCommand;
use PhpSchool\PhpWorkshop\Command\HelpCommand;
use PhpSchool\PhpWorkshop\Event\EventDispatcher;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\ExerciseRenderer;
use PhpSchool\PhpWorkshop\ExerciseRepository;
use PhpSchool\PhpWorkshop\Factory\MenuFactory;
use PhpSchool\PhpWorkshop\MenuItem\ResetProgress;
use PhpSchool\PhpWorkshop\UserState;
use PhpSchool\PhpWorkshop\UserStateSerializer;
use PhpSchool\PhpWorkshop\WorkshopType;
use PHPUnit_Framework_TestCase;

/**
 * Class MenuFactoryTest
 * @package PhpSchool\PhpWorkshopTest\Factory
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class MenuFactoryTest extends PHPUnit_Framework_TestCase
{
    public function testFactoryReturnsInstance()
    {
        $container = $this->createMock(ContainerInterface::class);
        $userStateSerializer = $this->createMock(UserStateSerializer::class);
        $userStateSerializer
            ->expects($this->once())
            ->method('deSerialize')
            ->will($this->returnValue(new UserState));

        $exerciseRepository = $this->createMock(ExerciseRepository::class);
        $exercise = $this->createMock(ExerciseInterface::class);
        $exercise->expects($this->exactly(2))
            ->method('getName')
            ->will($this->returnValue('Exercise'));
        $exerciseRepository
            ->expects($this->once())
            ->method('findAll')
            ->will($this->returnValue([$exercise]));
            
        $services = [
            UserStateSerializer::class => $userStateSerializer,
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
        ];
        
        $container
            ->expects($this->any())
            ->method('get')
            ->will($this->returnCallback(function ($name) use ($services) {
                return $services[$name];
            }));
        
        
        $factory = new MenuFactory;
        $this->assertInstanceOf(CliMenu::class, $factory($container));
    }
}
