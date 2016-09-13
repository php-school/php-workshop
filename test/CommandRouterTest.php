<?php

namespace PhpSchool\PhpWorkshopTest;

use Interop\Container\ContainerInterface;
use InvalidArgumentException;
use PhpSchool\PhpWorkshop\CommandArgument;
use PHPUnit_Framework_TestCase;
use PhpSchool\PhpWorkshop\CommandDefinition;
use PhpSchool\PhpWorkshop\CommandRouter;
use PhpSchool\PhpWorkshop\Exception\CliRouteNotExistsException;
use PhpSchool\PhpWorkshop\Exception\MissingArgumentException;
use RuntimeException;

/**
 * Class CommandRouterTest
 * @package PhpSchool\PhpWorkshopTest
 * @author  Aydin Hassan <aydin@hotmail.co.uk>
 */
class CommandRouterTest extends PHPUnit_Framework_TestCase
{
    public function testInvalidDefaultThrowsException()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Default command: "cmd" is not available');

        $c = $this->createMock(ContainerInterface::class);
        new CommandRouter([], 'cmd', $c);
    }

    public function testAddCommandThrowsExceptionIfCommandWithSameNameExists()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Command with name: "cmd" already exists');

        $c = $this->createMock(ContainerInterface::class);
        new CommandRouter([
            new CommandDefinition('cmd', [], 'service'),
            new CommandDefinition('cmd', [], 'service'),
        ], 'default', $c);
    }

    public function testConstruct()
    {
        $c = $this->createMock(ContainerInterface::class);
        new CommandRouter([new CommandDefinition('cmd', [], 'service'),], 'cmd', $c);
    }

    public function testRouteCommandWithNoArgsFromArrayUsesDefaultCommand()
    {
        $args = ['app'];

        $mock = $this->getMockBuilder('stdClass')
            ->setMethods(['__invoke'])
            ->getMock();

        $mock->expects($this->once())
            ->method('__invoke')
            ->will($this->returnValue(true));

        $c = $this->createMock(ContainerInterface::class);
        $router = new CommandRouter([new CommandDefinition('cmd', [], $mock),], 'cmd', $c);

        $router->route($args);
    }

    public function testRouteCommandWithNoArgsFromArgVUsesDefaultCommand()
    {
        $server = $_SERVER;
        $_SERVER['argv'] = ['app'];
        $mock = $this->getMockBuilder('stdClass')
            ->setMethods(['__invoke'])
            ->getMock();

        $mock->expects($this->once())
            ->method('__invoke')
            ->will($this->returnValue(true));

        $c = $this->createMock(ContainerInterface::class);
        $router = new CommandRouter([new CommandDefinition('cmd', [], $mock),], 'cmd', $c);

        $router->route();
        $_SERVER = $server;
    }

    public function testRouteCommandThrowsExceptionIfCommandWithNameNotExist()
    {
        $this->expectException(CliRouteNotExistsException::class);
        $this->expectExceptionMessage('Command: "not-a-cmd" does not exist');

        $c = $this->createMock(ContainerInterface::class);
        $router = new CommandRouter([new CommandDefinition('cmd', [], function () {
        }),], 'cmd', $c);
        $router->route(['app', 'not-a-cmd']);
    }

    public function testRouteCommandThrowsExceptionIfCommandIsMissingAllArguments()
    {
        $this->expectException(MissingArgumentException::class);
        $this->expectExceptionMessage('Command: "verify" is missing the following arguments: "exercise", "program"');

        $c = $this->createMock(ContainerInterface::class);
        $router = new CommandRouter(
            [new CommandDefinition('verify', ['exercise', 'program'], function () {
            }),],
            'verify',
            $c
        );
        $router->route(['app', 'verify']);
    }

    public function testRouteCommandThrowsExceptionIfCommandIsMissingArguments()
    {
        $this->expectException(MissingArgumentException::class);
        $this->expectExceptionMessage('Command: "verify" is missing the following arguments: "program"');

        $c = $this->createMock(ContainerInterface::class);
        $router = new CommandRouter(
            [new CommandDefinition('verify', ['exercise', 'program'], function () {
            }),],
            'verify',
            $c
        );
        $router->route(['app', 'verify', 'some-exercise']);
    }

    public function testRouteCommandWithArgs()
    {
        $mock = $this->getMockBuilder('stdClass')
            ->setMethods(['__invoke'])
            ->getMock();

        $mock->expects($this->once())
            ->method('__invoke')
            ->with('app', 'some-exercise', 'program.php')
            ->will($this->returnValue(true));

        $c = $this->createMock(ContainerInterface::class);
        $router = new CommandRouter(
            [new CommandDefinition('verify', ['exercise', 'program'], $mock),],
            'verify',
            $c
        );
        $router->route(['app', 'verify', 'some-exercise', 'program.php']);
    }

    public function testExceptionIsThrownIfCallableNotCallableAndNotContainerReference()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Callable must be a callable or a container entry for a callable service');

        $c = $this->createMock(ContainerInterface::class);
        $router = new CommandRouter(
            [new CommandDefinition('verify', ['exercise', 'program'], new \stdClass),],
            'verify',
            $c
        );
        $router->route(['app', 'verify', 'some-exercise', 'program.php']);
    }

    public function testExceptionIsThrownIfCallableNotCallableAndNotExistingContainerEntry()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Container has no entry named: "some.service"');

        $c = $this->createMock(ContainerInterface::class);

        $c
            ->expects($this->once())
            ->method('has')
            ->with('some.service')
            ->will($this->returnValue(false));

        $router = new CommandRouter(
            [new CommandDefinition('verify', ['exercise', 'program'], 'some.service'),],
            'verify',
            $c
        );
        $router->route(['app', 'verify', 'some-exercise', 'program.php']);
    }

    public function testExceptionIsThrownIfContainerEntryNotCallable()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Container entry: "some.service" not callable');

        $c = $this->createMock(ContainerInterface::class);

        $c
            ->expects($this->once())
            ->method('has')
            ->with('some.service')
            ->will($this->returnValue(true));

        $c
            ->expects($this->once())
            ->method('get')
            ->with('some.service')
            ->will($this->returnValue(null));

        $router = new CommandRouter(
            [new CommandDefinition('verify', ['exercise', 'program'], 'some.service'),],
            'verify',
            $c
        );
        $router->route(['app', 'verify', 'some-exercise', 'program.php']);
    }

    public function testCallableFromContainer()
    {
        $c = $this->createMock(ContainerInterface::class);

        $mock = $this->getMockBuilder('stdClass')
            ->setMethods(['__invoke'])
            ->getMock();

        $mock->expects($this->once())
            ->method('__invoke')
            ->with('app', 'some-exercise', 'program.php')
            ->will($this->returnValue(true));

        $c
            ->expects($this->once())
            ->method('has')
            ->with('some.service')
            ->will($this->returnValue(true));

        $c
            ->expects($this->once())
            ->method('get')
            ->with('some.service')
            ->will($this->returnValue($mock));

        $router = new CommandRouter(
            [new CommandDefinition('verify', ['exercise', 'program'], 'some.service'),],
            'verify',
            $c
        );
        $router->route(['app', 'verify', 'some-exercise', 'program.php']);
    }

    public function testCallableFromContainerWithIntegerReturnCode()
    {
        $c = $this->createMock(ContainerInterface::class);

        $mock = $this->getMockBuilder('stdClass')
            ->setMethods(['__invoke'])
            ->getMock();

        $mock->expects($this->once())
            ->method('__invoke')
            ->with('app', 'some-exercise', 'program.php')
            ->will($this->returnValue(10));

        $c
            ->expects($this->once())
            ->method('has')
            ->with('some.service')
            ->will($this->returnValue(true));

        $c
            ->expects($this->once())
            ->method('get')
            ->with('some.service')
            ->will($this->returnValue($mock));

        $router = new CommandRouter(
            [new CommandDefinition('verify', ['exercise', 'program'], 'some.service'),],
            'verify',
            $c
        );
        $res = $router->route(['app', 'verify', 'some-exercise', 'program.php']);
        $this->assertEquals(10, $res);
    }

    public function testRouteCommandSpeltIncorrectlyStillRoutes()
    {
        $mock = $this->getMockBuilder('stdClass')
            ->setMethods(['__invoke'])
            ->getMock();

        $mock->expects($this->once())
            ->method('__invoke')
            ->with('app', 'some-exercise', 'program.php')
            ->will($this->returnValue(true));

        $c = $this->createMock(ContainerInterface::class);
        $router = new CommandRouter(
            [new CommandDefinition('verify', ['exercise', 'program'], $mock),],
            'verify',
            $c
        );
        $router->route(['app', 'verifu', 'some-exercise', 'program.php']);
    }

    public function testRouteCommandWithOptionalArgument()
    {
        $mock = $this->getMockBuilder('stdClass')
            ->setMethods(['__invoke'])
            ->getMock();

        $mock->expects($this->at(0))
            ->method('__invoke')
            ->with('app', 'some-exercise')
            ->will($this->returnValue(true));

        $mock->expects($this->at(1))
            ->method('__invoke')
            ->with('app', 'some-exercise', 'program.php')
            ->will($this->returnValue(true));

        $c = $this->createMock(ContainerInterface::class);
        $router = new CommandRouter(
            [
                new CommandDefinition(
                    'verify',
                    [
                        'exercise',
                        new CommandArgument('program', true),
                        new CommandArgument('some-other-arg', true)
                    ],
                    $mock
                )
            ],
            'verify',
            $c
        );
        $router->route(['app', 'verify', 'some-exercise']);
        $router->route(['app', 'verify', 'some-exercise', 'program.php']);
        $router->route(['app', 'verify', 'some-exercise', 'program.php', 'some-other-arg-value']);
    }
}
