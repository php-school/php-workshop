<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Logger;

use Colors\Color;
use PhpSchool\PhpWorkshop\Output\OutputInterface;
use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;

class ConsoleLogger extends AbstractLogger implements LoggerInterface
{
    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var Color
     */
    private $color;

    public function __construct(OutputInterface $output, Color $color)
    {
        $this->output = $output;
        $this->color = $color;
    }

    public function log($level, $message, array $context = []): void
    {
        $parts = [
            sprintf(
                '%s - %s - %s',
                $this->color->fg('yellow', (new \DateTime())->format('H:i:s')),
                $this->color->bg('red', strtoupper($level)),
                $this->color->fg('red', $message)
            ),
            json_encode($context, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)
        ];

        $this->output->writeLine(implode("\n", $parts));
    }
}
