<?php

namespace PhpSchool\PhpWorkshopTest;

use Interop\Container\ContainerInterface;
use InvalidArgumentException;
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
        $this->setExpectedException(
            InvalidArgumentException::class,
            'Default command: "cmd" is not available'
        );

        $c = $this->getMock(ContainerInterface::class);
        new CommandRouter([], 'cmd', $c);
    }

    public function testAddCommandThrowsExceptionIfCommandWithSameNameExists()
    {
        $this->setExpectedException(
            InvalidArgumentException::class,
            'Command with name: "cmd" already exists'
        );

        $c = $this->getMock(ContainerInterface::class);
        new CommandRouter([
            new CommandDefinition('cmd', [], 'service'),
            new CommandDefinition('cmd', [], 'service'),
        ], 'default', $c);
    }

    public function testConstruct()
    {
        $c = $this->getMock(ContainerInterface::class);
        new CommandRouter([new CommandDefinition('cmd', [], 'service'),], 'cmd', $c);
    }

    public function testRouteCommandWithNoArgsFromArrayUsesDefaultCommand()
    {
        $args = ['app'];

        $mock = $this->getMock('stdClass', array('cb'));
        $mock->expects($this->once())
            ->method('cb')
            ->will($this->returnValue(true));

        $c = $this->getMock(ContainerInterface::class);
        $router = new CommandRouter([new CommandDefinition('cmd', [], [$mock, 'cb']),], 'cmd', $c);

        $router->route($args);
    }

    public function testRouteCommandWithNoArgsFromArgVUsesDefaultCommand()
    {
        $server = $_SERVER;
        $_SERVER['argv'] = ['app'];
        $mock = $this->getMock('stdClass', array('cb'));
        $mock->expects($this->once())
            ->method('cb')
            ->will($this->returnValue(true));

        $c = $this->getMock(ContainerInterface::class);
        $router = new CommandRouter([new CommandDefinition('cmd', [], [$mock, 'cb']),], 'cmd', $c);

        $router->route();
        $_SERVER = $server;
    }

    public function testRouteCommandThrowsExceptionIfCommandWithNameNotExist()
    {
        $this->setExpectedException(
            CliRouteNotExistsException::class,
            'Command: "not-a-cmd" does not exist'
        );

        $c = $this->getMock(ContainerInterface::class);
        $router = new CommandRouter([new CommandDefinition('cmd', [], function () {
        }),], 'cmd', $c);
        $router->route(['app', 'not-a-cmd']);
    }

    public function testRouteCommandThrowsExceptionIfCommandIsMissingAllArguments()
    {
        $this->setExpectedException(
            MissingArgumentException::class,
            'Command: "verify" is missing the following arguments: "exercise", "program"'
        );

        $c = $this->getMock(ContainerInterface::class);
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
        $this->setExpectedException(
            MissingArgumentException::class,
            'Command: "verify" is missing the following arguments: "program"'
        );

        $c = $this->getMock(ContainerInterface::class);
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
        $mock = $this->getMock('stdClass', array('cb'));
        $mock->expects($this->once())
            ->method('cb')
            ->with('app', 'some-exercise', 'program.php')
            ->will($this->returnValue(true));

        $c = $this->getMock(ContainerInterface::class);
        $router = new CommandRouter(
            [new CommandDefinition('verify', ['exercise', 'program'], [$mock, 'cb']),],
            'verify',
            $c
        );
        $router->route(['app', 'verify', 'some-exercise', 'program.php']);
    }

    public function testExceptionIsThrownIfCallableNotCallableAndNotContainerReference()
    {
        $this->setExpectedException(
            RuntimeException::class,
            'Callable must be a callable or a container entry for a callable service'
        );

        $c = $this->getMock(ContainerInterface::class);
        $router = new CommandRouter(
            [new CommandDefinition('verify', ['exercise', 'program'], new \stdClass),],
            'verify',
            $c
        );
        $router->route(['app', 'verify', 'some-exercise', 'program.php']);
    }

    public function testExceptionIsThrownIfCallableNotCallableAndNotExistingContainerEntry()
    {
        $this->setExpectedException(
            RuntimeException::class,
            'Container has no entry named: "some.service"'
        );

        $c = $this->getMock(ContainerInterface::class);

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
        $this->setExpectedException(
            RuntimeException::class,
            'Container entry: "some.service" not callable'
        );

        $c = $this->getMock(ContainerInterface::class);

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
        $c = $this->getMock(ContainerInterface::class);

        $mock = $this->getMock('stdClass', array('cb'));
        $mock->expects($this->once())
            ->method('cb')
            ->with('app', 'some-exercise', 'program.php')
            ->will($this->returnValue(true));

        $cb = [$mock, 'cb'];

        $c
            ->expects($this->once())
            ->method('has')
            ->with('some.service')
            ->will($this->returnValue(true));

        $c
            ->expects($this->once())
            ->method('get')
            ->with('some.service')
            ->will($this->returnValue($cb));

        $router = new CommandRouter(
            [new CommandDefinition('verify', ['exercise', 'program'], 'some.service'),],
            'verify',
            $c
        );
        $router->route(['app', 'verify', 'some-exercise', 'program.php']);
    }

    public function testCallableFromContainerWithIntegerReturnCode()
    {
        $c = $this->getMock(ContainerInterface::class);

        $mock = $this->getMock('stdClass', array('cb'));
        $mock->expects($this->once())
            ->method('cb')
            ->with('app', 'some-exercise', 'program.php')
            ->will($this->returnValue(10));

        $cb = [$mock, 'cb'];

        $c
            ->expects($this->once())
            ->method('has')
            ->with('some.service')
            ->will($this->returnValue(true));

        $c
            ->expects($this->once())
            ->method('get')
            ->with('some.service')
            ->will($this->returnValue($cb));

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
        $mock = $this->getMock('stdClass', array('cb'));
        $mock->expects($this->once())
            ->method('cb')
            ->with('app', 'some-exercise', 'program.php')
            ->will($this->returnValue(true));

        $c = $this->getMock(ContainerInterface::class);
        $router = new CommandRouter(
            [new CommandDefinition('verify', ['exercise', 'program'], [$mock, 'cb']),],
            'verify',
            $c
        );
        $router->route(['app', 'verifu', 'some-exercise', 'program.php']);
    }
}
