<?php

namespace PhpSchool\PhpWorkshopTest\Factory;

use Interop\Container\ContainerInterface;
use PhpSchool\PhpWorkshop\Exception\InvalidArgumentException;
use PhpSchool\PhpWorkshop\Exercise\ExerciseType;
use PhpSchool\PhpWorkshop\ExerciseRunner\ExerciseRunnerInterface;
use PhpSchool\PhpWorkshop\Factory\RunnerFactory;
use PHPUnit_Framework_TestCase;

/**
 * Class RunnerFactoryTest
 * @package PhpSchool\PhpWorkshopTest\Factory
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class RunnerFactoryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function setUp()
    {
        $this->container = $this->getMock(ContainerInterface::class);
    }

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

        (new RunnerFactory($this->container))->create($type);
    }

    public function testCliAndCgiRunnerCanBeCreated()
    {
        $cliType = new ExerciseType(ExerciseType::CLI);
        $cgiType = new ExerciseType(ExerciseType::CGI);

        $cliRunner = $this->getMock(ExerciseRunnerInterface::class);
        $cgiRunner = $this->getMock(ExerciseRunnerInterface::class);
        $this->container
            ->method('get')
            ->will($this->returnValueMap([
                [$cliType->getValue(), $cliRunner],
                [$cgiType->getValue(), $cgiRunner],
            ]));

        $runnerFactory = new RunnerFactory($this->container);

        $this->assertSame($cliRunner, $runnerFactory->create($cliType));
        $this->assertSame($cgiRunner, $runnerFactory->create($cgiType));
    }
}
