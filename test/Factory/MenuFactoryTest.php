<?php

namespace PhpSchool\PhpWorkshopTest\Factory;

use Interop\Container\ContainerInterface;
use PhpSchool\CliMenu\CliMenu;
use PhpSchool\PhpWorkshop\Command\CreditsCommand;
use PhpSchool\PhpWorkshop\Command\HelpCommand;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\ExerciseRenderer;
use PhpSchool\PhpWorkshop\ExerciseRepository;
use PhpSchool\PhpWorkshop\Factory\MenuFactory;
use PhpSchool\PhpWorkshop\MenuItem\ResetProgress;
use PhpSchool\PhpWorkshop\UserState;
use PhpSchool\PhpWorkshop\UserStateSerializer;
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
        $container = $this->getMock(ContainerInterface::class);
        
        $userStateSerializer = $this->getMockBuilder(UserStateSerializer::class)
            ->disableOriginalConstructor()
            ->getMock();
        
        $userStateSerializer
            ->expects($this->once())
            ->method('deSerialize')
            ->will($this->returnValue(new UserState));

        $exerciseRepository = $this->getMockBuilder(ExerciseRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $exercise = $this->getMock(ExerciseInterface::class);
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
            ExerciseRenderer::class => $this->getMockBuilder(ExerciseRenderer::class)
                ->disableOriginalConstructor()
                ->getMock(),
            HelpCommand::class => $this->getMockBuilder(HelpCommand::class)
                ->disableOriginalConstructor()
                ->getMock(),
            CreditsCommand::class => $this->getMockBuilder(CreditsCommand::class)
                ->disableOriginalConstructor()
                ->getMock(),
            ResetProgress::class => $this->getMockBuilder(ResetProgress::class)
                ->disableOriginalConstructor()
                ->getMock(),
            'workshopLogo'  => 'LOGO',
            'bgColour'      => 'black',
            'fgColour'      => 'green',
            'workshopTitle' => 'TITLE',
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
