<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Logger;

use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;

class Logger extends AbstractLogger implements LoggerInterface
{
    /**
     * @var string
     */
    private $filePath;

    public function __construct(string $filePath)
    {
        $this->filePath = $filePath;
    }

    /**
     * @param string $level
     */
    public function log($level, $message, array $context = []): void
    {
        if (!file_exists(dirname($this->filePath))) {
            if (!mkdir($concurrentDirectory = dirname($this->filePath), 0777, true) && !is_dir($concurrentDirectory)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
            }
        }

        file_put_contents(
            $this->filePath,
            sprintf(
                "Time: %s, Level: %s, Message: %s, Context: %s\n\n",
                (new \DateTime())->format('d-m-y H:i:s'),
                $level,
                $message,
                json_encode($context),
            ),
            FILE_APPEND,
        );
    }
}
