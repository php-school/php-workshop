<?php

namespace PhpSchool\PhpWorkshopTest;

use Interop\Container\ContainerInterface;
use InvalidArgumentException;
use PhpSchool\PhpWorkshop\CommandArgument;
use PhpSchool\PhpWorkshop\Event\EventDispatcher;
use PhpSchool\PhpWorkshop\Input\Input;
use PHPUnit\Framework\TestCase;
use PhpSchool\PhpWorkshop\CommandDefinition;
use PhpSchool\PhpWorkshop\CommandRouter;
use PhpSchool\PhpWorkshop\Exception\CliRouteNotExistsException;
use PhpSchool\PhpWorkshop\Exception\MissingArgumentException;
use Prophecy\PhpUnit\ProphecyTrait;
use RuntimeException;

/**
 * Class CommandRouterTest
 * @package PhpSchool\PhpWorkshopTest
 * @author  Aydin Hassan <aydin@hotmail.co.uk>
 */
class CommandRouterTest extends TestCase
{
    use ProphecyTrait;

    public function testInvalidDefaultThrowsException() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Default command: "cmd" is not available');

        $c = $this->createMock(ContainerInterface::class);
        $eventDispatcher = $this->createMock(EventDispatcher::class);
        new CommandRouter([], 'cmd', $eventDispatcher, $c);
    }

    public function testAddCommandThrowsExceptionIfCommandWithSameNameExists() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Command with name: "cmd" already exists');

        $c = $this->createMock(ContainerInterface::class);
        $eventDispatcher = $this->createMock(EventDispatcher::class);
        new CommandRouter([
            new CommandDefinition('cmd', [], 'service'),
            new CommandDefinition('cmd', [], 'service'),
        ], 'default', $eventDispatcher, $c);
    }

    public function testRouteCommandWithNoArgsFromArrayUsesDefaultCommand() : void
    {
        $args = ['app'];

        $mock = $this->getMockBuilder('stdClass')
            ->setMethods(['__invoke'])
            ->getMock();

        $mock->expects($this->once())
            ->method('__invoke')
            ->willReturn(true);

        $c = $this->createMock(ContainerInterface::class);
        $eventDispatcher = $this->createMock(EventDispatcher::class);
        $router = new CommandRouter([new CommandDefinition('cmd', [], $mock),], 'cmd', $eventDispatcher, $c);

        $router->route($args);
    }

    public function testRouteCommandWithNoArgsFromArgVUsesDefaultCommand() : void
    {
        $server = $_SERVER;
        $_SERVER['argv'] = ['app'];
        $mock = $this->getMockBuilder('stdClass')
            ->setMethods(['__invoke'])
            ->getMock();

        $mock->expects($this->once())
            ->method('__invoke')
            ->willReturn(true);

        $c = $this->createMock(ContainerInterface::class);
        $eventDispatcher = $this->createMock(EventDispatcher::class);
        $router = new CommandRouter([new CommandDefinition('cmd', [], $mock),], 'cmd', $eventDispatcher, $c);

        $router->route();
        $_SERVER = $server;
    }

    public function testRouteCommandThrowsExceptionIfCommandWithNameNotExist() : void
    {
        $this->expectException(CliRouteNotExistsException::class);
        $this->expectExceptionMessage('Command: "not-a-cmd" does not exist');

        $c = $this->createMock(ContainerInterface::class);
        $eventDispatcher = $this->createMock(EventDispatcher::class);

        $router = new CommandRouter([new CommandDefinition('cmd', [], function () {
        }),], 'cmd', $eventDispatcher, $c);
        $router->route(['app', 'not-a-cmd']);
    }

    public function testRouteCommandThrowsExceptionIfCommandIsMissingAllArguments() : void
    {
        $this->expectException(MissingArgumentException::class);
        $this->expectExceptionMessage('Command: "verify" is missing the following arguments: "exercise", "program"');

        $c = $this->createMock(ContainerInterface::class);
        $eventDispatcher = $this->createMock(EventDispatcher::class);
        $router = new CommandRouter(
            [new CommandDefinition('verify', ['exercise', 'program'], function () {
            }),],
            'verify',
            $eventDispatcher,
            $c
        );
        $router->route(['app', 'verify']);
    }

    public function testRouteCommandThrowsExceptionIfCommandIsMissingArguments() : void
    {
        $this->expectException(MissingArgumentException::class);
        $this->expectExceptionMessage('Command: "verify" is missing the following arguments: "program"');

        $c = $this->createMock(ContainerInterface::class);
        $eventDispatcher = $this->createMock(EventDispatcher::class);
        $router = new CommandRouter(
            [new CommandDefinition('verify', ['exercise', 'program'], function () {
            }),],
            'verify',
            $eventDispatcher,
            $c
        );
        $router->route(['app', 'verify', 'some-exercise']);
    }

    public function testRouteCommandWithArgs() : void
    {
        $mock = $this->getMockBuilder('stdClass')
            ->setMethods(['__invoke'])
            ->getMock();

        $mock->expects($this->once())
            ->method('__invoke')
            ->with($this->callback(function (Input $input) {
                return $input->getAppName() === 'app'
                    && $input->getArgument('exercise') === 'some-exercise'
                    && $input->getArgument('program') === 'program.php';
            }))
            ->willReturn(true);

        $c = $this->createMock(ContainerInterface::class);
        $eventDispatcher = $this->createMock(EventDispatcher::class);
        $router = new CommandRouter(
            [new CommandDefinition('verify', ['exercise', 'program'], $mock),],
            'verify',
            $eventDispatcher,
            $c
        );
        $router->route(['app', 'verify', 'some-exercise', 'program.php']);
    }

    public function testExceptionIsThrownIfCallableNotCallableAndNotContainerReference() : void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Callable must be a callable or a container entry for a callable service');

        $c = $this->createMock(ContainerInterface::class);
        $eventDispatcher = $this->createMock(EventDispatcher::class);
        $router = new CommandRouter(
            [new CommandDefinition('verify', ['exercise', 'program'], new \stdClass),],
            'verify',
            $eventDispatcher,
            $c
        );
        $router->route(['app', 'verify', 'some-exercise', 'program.php']);
    }

    public function testExceptionIsThrownIfCallableNotCallableAndNotExistingContainerEntry() : void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Container has no entry named: "some.service"');

        $c = $this->createMock(ContainerInterface::class);
        $eventDispatcher = $this->createMock(EventDispatcher::class);

        $c
            ->expects($this->once())
            ->method('has')
            ->with('some.service')
            ->willReturn(false);

        $router = new CommandRouter(
            [new CommandDefinition('verify', ['exercise', 'program'], 'some.service'),],
            'verify',
            $eventDispatcher,
            $c
        );
        $router->route(['app', 'verify', 'some-exercise', 'program.php']);
    }

    public function testExceptionIsThrownIfContainerEntryNotCallable() : void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Container entry: "some.service" not callable');

        $c = $this->createMock(ContainerInterface::class);
        $eventDispatcher = $this->createMock(EventDispatcher::class);

        $c
            ->expects($this->once())
            ->method('has')
            ->with('some.service')
            ->willReturn(true);

        $c
            ->expects($this->once())
            ->method('get')
            ->with('some.service')
            ->willReturn(null);

        $router = new CommandRouter(
            [new CommandDefinition('verify', ['exercise', 'program'], 'some.service'),],
            'verify',
            $eventDispatcher,
            $c
        );
        $router->route(['app', 'verify', 'some-exercise', 'program.php']);
    }

    public function testCallableFromContainer() : void
    {
        $c = $this->createMock(ContainerInterface::class);

        $mock = $this->getMockBuilder('stdClass')
            ->setMethods(['__invoke'])
            ->getMock();

        $mock->expects($this->once())
            ->method('__invoke')
            ->with($this->callback(function (Input $input) {
                return $input->getAppName() === 'app'
                    && $input->getArgument('exercise') === 'some-exercise'
                    && $input->getArgument('program') === 'program.php';
            }))
            ->willReturn(true);

        $c
            ->expects($this->once())
            ->method('has')
            ->with('some.service')
            ->willReturn(true);

        $c
            ->expects($this->once())
            ->method('get')
            ->with('some.service')
            ->willReturn($mock);

        $eventDispatcher = $this->createMock(EventDispatcher::class);

        $router = new CommandRouter(
            [new CommandDefinition('verify', ['exercise', 'program'], 'some.service'),],
            'verify',
            $eventDispatcher,
            $c
        );
        $router->route(['app', 'verify', 'some-exercise', 'program.php']);
    }

    public function testCallableFromContainerWithIntegerReturnCode() : void
    {
        $c = $this->createMock(ContainerInterface::class);

        $mock = $this->getMockBuilder('stdClass')
            ->setMethods(['__invoke'])
            ->getMock();

        $mock->expects($this->once())
            ->method('__invoke')
            ->with($this->callback(function (Input $input) {
                return $input->getAppName() === 'app'
                    && $input->getArgument('exercise') === 'some-exercise'
                    && $input->getArgument('program') === 'program.php';
            }))
            ->willReturn(10);

        $c
            ->expects($this->once())
            ->method('has')
            ->with('some.service')
            ->willReturn(true);

        $c
            ->expects($this->once())
            ->method('get')
            ->with('some.service')
            ->willReturn($mock);

        $eventDispatcher = $this->createMock(EventDispatcher::class);

        $router = new CommandRouter(
            [new CommandDefinition('verify', ['exercise', 'program'], 'some.service'),],
            'verify',
            $eventDispatcher,
            $c
        );
        $res = $router->route(['app', 'verify', 'some-exercise', 'program.php']);
        $this->assertEquals(10, $res);
    }

    public function testRouteCommandSpeltIncorrectlyStillRoutes() : void
    {
        $mock = $this->getMockBuilder('stdClass')
            ->setMethods(['__invoke'])
            ->getMock();

        $mock->expects($this->once())
            ->method('__invoke')
            ->with($this->callback(function (Input $input) {
                return $input->getAppName() === 'app'
                && $input->getArgument('exercise') === 'some-exercise'
                && $input->getArgument('program') === 'program.php';
            }))
            ->willReturn(true);

        $c = $this->createMock(ContainerInterface::class);
        $eventDispatcher = $this->createMock(EventDispatcher::class);

        $router = new CommandRouter(
            [new CommandDefinition('verify', ['exercise', 'program'], $mock),],
            'verify',
            $eventDispatcher,
            $c
        );
        $router->route(['app', 'verifu', 'some-exercise', 'program.php']);
    }

    public function testRouteCommandWithOptionalArgument() : void
    {
        $mock = $this->getMockBuilder('stdClass')
            ->setMethods(['__invoke'])
            ->getMock();

        $mock->expects($this->exactly(3))
            ->method('__invoke')
            ->withConsecutive(
                [
                    $this->callback(function (Input $input) {
                        return $input->getAppName() === 'app'
                            && $input->getArgument('exercise') === 'some-exercise';
                    })
                ],
                [
                    $this->callback(function (Input $input) {
                        return $input->getAppName() === 'app'
                            && $input->getArgument('exercise') === 'some-exercise'
                            && $input->getArgument('program') === 'program.php';
                    })
                ],
                [
                    $this->callback(function (Input $input) {
                        return $input->getAppName() === 'app'
                            && $input->getArgument('exercise') === 'some-exercise'
                            && $input->getArgument('program') === 'program.php'
                            && $input->getArgument('some-other-arg') === 'some-other-arg-value';
                    })
                ]
            )
            ->willReturnOnConsecutiveCalls(true, true);

        $c = $this->createMock(ContainerInterface::class);
        $eventDispatcher = $this->createMock(EventDispatcher::class);
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
            $eventDispatcher,
            $c
        );
        $router->route(['app', 'verify', 'some-exercise']);
        $router->route(['app', 'verify', 'some-exercise', 'program.php']);
        $router->route(['app', 'verify', 'some-exercise', 'program.php', 'some-other-arg-value']);
    }
}
