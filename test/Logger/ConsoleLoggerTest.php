<?php

namespace PhpSchool\PhpWorkshopTest\Logger;

use PhpSchool\CliMenu\Util\StringUtil;
use PhpSchool\PhpWorkshop\Logger\ConsoleLogger;
use PhpSchool\PhpWorkshopTest\ContainerAwareTest;
use Psr\Log\LoggerInterface;
use Yoast\PHPUnitPolyfills\Polyfills\AssertionRenames;

class ConsoleLoggerTest extends ContainerAwareTest
{
    use AssertionRenames;

    public function setUp(): void
    {
        parent::setUp();

        $this->container->set('phpschoolGlobalDir', $this->getTemporaryDirectory());
        $this->container->set('appName', 'my-workshop');
        $this->container->set('basePath', __DIR__ . '/../');
        $this->container->set('debugMode', true);
    }

    public function testConsoleLoggerIsCreatedIfDebugModeEnable(): void
    {
        $this->assertInstanceOf(ConsoleLogger::class, $this->container->get(LoggerInterface::class));
    }

    public function testLoggerWithContext(): void
    {
        $logger = $this->container->get(LoggerInterface::class);
        $logger->critical('Failed to copy file', ['exercise' => 'my-exercise']);

        $out = StringUtil::stripAnsiEscapeSequence($this->getActualOutputForAssertion());

        $match  = '/\d{2}\:\d{2}\:\d{2} - CRITICAL - Failed to copy file\n{\n    "exercise": "my-exercise"\n}/';
        $this->assertMatchesRegularExpression($match, $out);
    }
}
