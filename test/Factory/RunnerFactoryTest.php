<?php

namespace PhpSchool\PhpWorkshopTest\Factory;

use Interop\Container\ContainerInterface;
use PhpSchool\PhpWorkshop\Event\EventDispatcher;
use PhpSchool\PhpWorkshop\Exception\InvalidArgumentException;
use PhpSchool\PhpWorkshop\Exercise\ExerciseType;
use PhpSchool\PhpWorkshop\ExerciseRunner\CgiRunner;
use PhpSchool\PhpWorkshop\ExerciseRunner\CliRunner;
use PhpSchool\PhpWorkshop\Factory\RunnerFactory;
use PHPUnit_Framework_TestCase;

/**
 * Class RunnerFactoryTest
 * @package PhpSchool\PhpWorkshopTest\Factory
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class RunnerFactoryTest extends PHPUnit_Framework_TestCase
{

    public function testExceptionIsThrownIfTypeNotSupported()
    {
        $type = $this->getMockBuilder(ExerciseType::class)
            ->disableOriginalConstructor()
            ->getMock();
        $type
            ->expects($this->exactly(2))
            ->method('getValue')
            ->will($this->returnValue('invalid'));

        $this->setExpectedException(InvalidArgumentException::class, 'Exercise Type: "invalid" not supported');

        (new RunnerFactory)->create($type, new EventDispatcher);
    }

    public function testCliAndCgiRunnerCanBeCreated()
    {
        $cliType = new ExerciseType(ExerciseType::CLI);
        $cgiType = new ExerciseType(ExerciseType::CGI);

        $runnerFactory = new RunnerFactory($this->container);

        $eventDispatcher = new EventDispatcher;
        $this->assertInstanceOf(CliRunner::class, $runnerFactory->create($cliType, $eventDispatcher));
        $this->assertInstanceOf(CgiRunner::class, $runnerFactory->create($cgiType, $eventDispatcher));
    }
}
