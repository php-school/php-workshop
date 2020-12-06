<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshopTest;

use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;

class MockLogger extends AbstractLogger implements LoggerInterface
{
    /**
     * @var array<array{level: string, message: string, context: array<mixed>}>
     */
    public $messages = [];

    public function log($level, $message, array $context = []): void
    {
        $this->messages[] = [
            'level' => $level,
            'message' => $message,
            'context' => $context
        ];
    }

    /**
     * @return array<array{level: string, message: string, context: array<mixed>}>
     */
    public function getLastMessage(): array
    {
        return $this->messages[count($this->messages) - 1];
    }
}
