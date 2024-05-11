<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshopTest\Logger;

use PhpSchool\PhpWorkshopTest\ContainerAwareTest;
use Psr\Log\LoggerInterface;
use Yoast\PHPUnitPolyfills\Polyfills\AssertionRenames;

class LoggerTest extends ContainerAwareTest
{
    use AssertionRenames;

    public function setUp(): void
    {
        parent::setUp();

        $this->container->set('phpschoolGlobalDir', $this->getTemporaryDirectory());
        $this->container->set('appName', 'my-workshop');
        $this->container->set('debugMode', false);
    }

    public function testLoggerDoesNotCreateFileIfNoMessageIsLogged(): void
    {
        $expectedFileName = sprintf("%s/logs/my-workshop.log", $this->getTemporaryDirectory());

        $logger = $this->container->get(LoggerInterface::class);

        $this->assertFileDoesNotExist($expectedFileName);
    }

    public function testLoggerCreatesFileWhenMessageIsLogged(): void
    {
        $expectedFileName = sprintf("%s/logs/my-workshop.log", $this->getTemporaryDirectory());

        $logger = $this->container->get(LoggerInterface::class);
        $logger->critical('Failed to copy file');

        $this->assertFileExists($expectedFileName);
        $match  = '/Time\: \d{2}\-\d{2}\-\d{2} \d{2}\:\d{2}\:\d{2}, Level\: critical, Message\: Failed to copy file,';
        $match .= ' Context\: \[\]' . "\n\n/";

        $this->assertMatchesRegularExpression(
            $match,
            file_get_contents($expectedFileName),
        );
    }

    public function testLoggerAppendsToFileWhenSecondMessageIsLogged(): void
    {
        $expectedFileName = sprintf("%s/logs/my-workshop.log", $this->getTemporaryDirectory());

        $logger = $this->container->get(LoggerInterface::class);
        $logger->critical('Failed to copy file');
        $logger->emergency('Second error');

        $this->assertFileExists($expectedFileName);
        $match  = '/Time\: \d{2}\-\d{2}\-\d{2} \d{2}\:\d{2}\:\d{2}, Level\: critical, Message\: Failed to copy file,';
        $match .= ' Context\: \[\]' . "\n\n";
        $match .= 'Time\: \d{2}\-\d{2}\-\d{2} \d{2}\:\d{2}\:\d{2}, Level\: emergency, Message\: Second error,';
        $match .= ' Context\: \[\]' . "\n\n/";

        $this->assertMatchesRegularExpression(
            $match,
            file_get_contents($expectedFileName),
        );
    }

    public function testLoggerAppendsToFileWhenItAlreadyExists(): void
    {
        $expectedFileName = sprintf("%s/logs/my-workshop.log", $this->getTemporaryDirectory());

        mkdir(dirname($expectedFileName), 0777, true);
        file_put_contents($expectedFileName, "Please do not overwrite me\n\n");

        $logger = $this->container->get(LoggerInterface::class);
        $logger->emergency('Second error');

        $this->assertFileExists($expectedFileName);
        $match  = '/Please do not overwrite me' . "\n\n";
        $match .= 'Time\: \d{2}\-\d{2}\-\d{2} \d{2}\:\d{2}\:\d{2}, Level\: emergency, Message\: Second error,';
        $match .= ' Context\: \[\]' . "\n\n/";

        $this->assertMatchesRegularExpression(
            $match,
            file_get_contents($expectedFileName),
        );
    }

    public function testLoggerWithContextIsEncoded(): void
    {
        $expectedFileName = sprintf("%s/logs/my-workshop.log", $this->getTemporaryDirectory());

        $logger = $this->container->get(LoggerInterface::class);
        $logger->critical('Failed to copy file', ['exercise' => 'my-exercise']);

        $this->assertFileExists($expectedFileName);

        $match  = '/Time\: \d{2}\-\d{2}\-\d{2} \d{2}\:\d{2}\:\d{2}, Level\: critical, Message\: Failed to copy file, ';
        $match .= 'Context\: {"exercise":"my-exercise"}/';
        $this->assertMatchesRegularExpression(
            $match,
            file_get_contents($expectedFileName),
        );
    }
}
