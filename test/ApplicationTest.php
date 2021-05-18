<?php

/** @noinspection PhpFullyQualifiedNameUsageInspection */

namespace PhpSchool\PhpWorkshopTest;

use PhpSchool\PhpWorkshop\Application;
use PhpSchool\PhpWorkshop\CommandDefinition;
use PhpSchool\PhpWorkshop\CommandRouter;
use PhpSchool\PhpWorkshop\Event\EventDispatcher;
use PhpSchool\PhpWorkshop\Exception\InvalidArgumentException;
use PhpSchool\PhpWorkshop\Exception\RuntimeException;
use PhpSchool\PhpWorkshop\Output\NullOutput;
use PhpSchool\PhpWorkshop\Output\OutputInterface;
use PhpSchool\PhpWorkshopTest\Asset\MockEventDispatcher;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use PhpSchool\PhpWorkshop\Logger\ConsoleLogger;
use PhpSchool\PhpWorkshop\Logger\Logger;
use PHPUnit\Framework\TestCase;

class ApplicationTest extends BaseTest
{
    public function testEventListenersFromLocalAndWorkshopConfigAreMerged(): void
    {
        $frameworkFileContent = <<<'FRAME'
        <?php return [
            'eventListeners' => [
                'event1' => [
                    'entry1',
                    'entry2',
                ]
            ]
        ];
FRAME;

        $localFileContent = <<<'LOCAL'
        <?php return [
            'eventListeners' => [
                'event1' => [
                    'entry3',
                ]
            ]
        ];
LOCAL;

        $localFile = $this->getTemporaryFile(uniqid($this->getName(), true), $localFileContent);
        $frameworkFile = $this->getTemporaryFile(uniqid($this->getName(), true), $frameworkFileContent);

        $app = new Application('Test App', $localFile);

        $rm = new \ReflectionMethod($app, 'getContainer');
        $rm->setAccessible(true);

        $rp = new \ReflectionProperty(Application::class, 'frameworkConfigLocation');
        $rp->setAccessible(true);
        $rp->setValue($app, $frameworkFile);

        $container = $rm->invoke($app, false);

        $eventListeners = $container->get('eventListeners');

        self::assertEquals(
            [
                'event1' => [
                    'entry1',
                    'entry2',
                    'entry3',
                ]
            ],
            $eventListeners
        );
    }

    public function testExceptionIsThrownIfConfigFileDoesNotExist(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('File "not-existing-file.php" was expected to exist.');

        new Application('My workshop', 'not-existing-file.php');
    }

    public function testExceptionIsThrownIfResultClassDoesNotExist(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Class "NotExistingClass" does not exist');

        $app = new Application('My workshop', __DIR__ . '/../app/config.php');
        $app->addResult(\NotExistingClass::class, \NotExistingClass::class);
    }

    public function testExceptionIsThrownIfResultRendererClassDoesNotExist(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Class "NotExistingClass" does not exist');

        $app = new Application('My workshop', __DIR__ . '/../app/config.php');
        $app->addResult(\PhpSchool\PhpWorkshop\Result\Success::class, \NotExistingClass::class);
    }

    public function testTearDownEventIsFiredOnApplicationException(): void
    {
        $configFile = $this->getTemporaryFile('config.php', '<?php return [];');
        $application = new Application('Testing TearDown', $configFile);

        $container = $application->configure();
        $container->set('basePath', __DIR__);
        $container->set(EventDispatcher::class, new MockEventDispatcher());
        $container->set(OutputInterface::class, new NullOutput());

        /** @var MockEventDispatcher $eventDispatcher */
        $eventDispatcher = $container->get(EventDispatcher::class);

        $commandRouter = $container->get(CommandRouter::class);
        $commandRouter->addCommand(new CommandDefinition('Failure', [], function () {
            throw new RuntimeException('We failed somewhere...');
        }));

        $_SERVER['argv'] = [$this->getName(), 'Failure'];

        $application->run();

        self::assertSame(1, $eventDispatcher->getEventDispatchCount('application.tear-down'));
    }

    public function testLoggingExceptionDuringTearDown(): void
    {
        $configFile = $this->getTemporaryFile('config.php', '<?php return [];');
        $application = new Application('Testing tear down logging', $configFile);
        $exception = new \Exception('Unexpected error');

        $container = $application->configure();
        $container->set('basePath', __DIR__);
        $container->set(OutputInterface::class, new NullOutput());
        $container->set(LoggerInterface::class, new MockLogger());
        $container->set('eventListeners', [
            'testing-failure-logging' => [
                'application.tear-down' => [
                    static function () use ($exception) {
                        throw $exception;
                    },
                ]
            ]
        ]);

        $commandRouter = $container->get(CommandRouter::class);
        $commandRouter->addCommand(new CommandDefinition('Failure', [], function () {
            throw new RuntimeException('We failed somewhere...');
        }));

        $application->run();

        /** @var MockLogger $logger */
        $logger = $container->get(LoggerInterface::class);
        self::assertCount(1, $logger->messages);
        self::assertSame('Unexpected error', $logger->messages[0]['message']);
        self::assertSame($exception, $logger->messages[0]['context']['exception']);
    }

    public function testConfigureReturnsSameContainerInstance(): void
    {
        $configFile = $this->getTemporaryFile('config.php', '<?php return [];');
        $application = new Application('Testing Configure', $configFile);

        self::assertSame($application->configure(), $application->configure());
    }

    public function testDebugFlagSwitchesLoggerToConsoleLogger(): void
    {
        $app = new Application('My workshop', __DIR__ . '/../app/config.php');

        $frameworkFile = sprintf('%s/%s', sys_get_temp_dir(), uniqid($this->getName(), true));
        file_put_contents($frameworkFile, '<?php return []; ');

        $rp = new \ReflectionProperty(Application::class, 'frameworkConfigLocation');
        $rp->setAccessible(true);
        $rp->setValue($app, $frameworkFile);

        $rm = new \ReflectionMethod($app, 'getContainer');
        $rm->setAccessible(true);

        $container = $rm->invoke($app, true);

        $container->set('phpschoolGlobalDir', $this->getTemporaryDirectory());
        $container->set('appName', 'my-workshop');

        $logger = $container->get(LoggerInterface::class);
        self::assertInstanceOf(ConsoleLogger::class, $logger);
    }
}
