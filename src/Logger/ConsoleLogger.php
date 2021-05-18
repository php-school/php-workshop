<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Logger;

use PhpSchool\PhpWorkshop\Output\OutputInterface;
use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;

class ConsoleLogger extends AbstractLogger implements LoggerInterface
{
    /**
     * @var OutputInterface
     */
    private $output;

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function log($level, $message, array $context = []): void
    {
        $this->output->writeLine(sprintf(
            "Time: %s, Level: %s, Message: %s, Context: %s",
            (new \DateTime())->format('d-m-y H:i:s'),
            $level,
            $message,
            json_encode($context)
        ));
    }
}
