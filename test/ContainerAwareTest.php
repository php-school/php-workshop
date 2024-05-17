<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshopTest;

use DI\ContainerBuilder;
use PHPUnit\Framework\ExpectationFailedException;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;

abstract class ContainerAwareTest extends BaseTest
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var ?string
     */
    private $currentWorkingDirectory;

    public function setUp(): void
    {
        $containerConfig = __DIR__ . '/../app/config.php';

        $containerBuilder = new ContainerBuilder();
        $containerBuilder->addDefinitions(require $containerConfig);

        $containerBuilder->useAutowiring(false);
        $containerBuilder->useAnnotations(false);

        $this->container = $containerBuilder->build();
    }

    public function mockLogger(): void
    {
        $this->container->set(LoggerInterface::class, new MockLogger());
    }

    public function mockCurrentWorkingDirectory(): void
    {
        $this->currentWorkingDirectory = $this->getTemporaryDirectory();
        $this->container->set('currentWorkingDirectory', $this->currentWorkingDirectory);
    }

    public function getCurrentWorkingDirectory(): ?string
    {
        return $this->currentWorkingDirectory;
    }

    /**
     * @param array<array{level: string, message: string, context: array<mixed>}> $messages
     * @throws ExpectationFailedException
     */
    public function assertLoggerHasMessages(array $messages): void
    {
        $logged = $this->container->get(LoggerInterface::class)->messages;

        foreach ($messages as $message) {
            $this->assertContains(
                [
                    'level' => $message['level'],
                    'message' => $message['message'],
                    'context' => $message['context'],
                ],
                $logged,
            );
        }
    }

    public function tearDown(): void
    {
        if ($this->currentWorkingDirectory) {
            (new Filesystem())->remove($this->currentWorkingDirectory);
        }

        parent::tearDown();
    }
}
