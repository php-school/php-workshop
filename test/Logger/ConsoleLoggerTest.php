<?php

namespace PhpSchool\PhpWorkshopTest\Logger;

use PhpSchool\CliMenu\Util\StringUtil;
use PhpSchool\PhpWorkshop\Logger\ConsoleLogger;
use PhpSchool\PhpWorkshop\Utils\StringUtils;
use PhpSchool\PhpWorkshopTest\ContainerAwareTest;
use Psr\Log\LoggerInterface;

class ConsoleLoggerTest extends ContainerAwareTest
{
    public function setUp(): void
    {
        parent::setUp();

        $this->container->set('phpschoolGlobalDir', $this->getTemporaryDirectory());
        $this->container->set('appName', 'my-workshop');
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
