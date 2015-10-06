<?php

namespace PhpWorkshop\PhpWorkshopTest;

use DI\Container;
use Interop\Container\ContainerInterface;
use InvalidArgumentException;
use PHPUnit_Framework_TestCase;
use PhpWorkshop\PhpWorkshop\CommandDefinition;
use PhpWorkshop\PhpWorkshop\CommandRouter;
use PhpWorkshop\PhpWorkshop\Exception\CliRouteNotExists;
use PhpWorkshop\PhpWorkshop\Exception\MissingArgumentException;

/**
 * Class CommandRouterTest
 * @package PhpWorkshop\PhpWorkshopTest
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
            CliRouteNotExists::class,
            'Command: "not-a-cmd" does not exist'
        );

        $c = $this->getMock(ContainerInterface::class);
        $router = new CommandRouter([new CommandDefinition('cmd', [], function () {}),], 'cmd', $c);
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
            [new CommandDefinition('verify', ['exercise', 'program'], function () {}),],
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
            [new CommandDefinition('verify', ['exercise', 'program'], function () {}),],
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
}
