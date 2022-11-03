<?php

namespace PhpSchool\PhpWorkshop\Output;

use Throwable;

class BufferedOutput implements OutputInterface
{
    /**
     * @var string
     */
    private $buffer = '';

    public function printError(string $error): void
    {
        // noop
    }

    public function printException(Throwable $exception): void
    {
        // noop
    }

    public function write(string $content): void
    {
        $this->buffer .= $content;
    }

    public function writeLines(array $lines): void
    {
        foreach ($lines as $line) {
            $this->writeLine($line);
        }
    }

    public function writeLine(string $line): void
    {
        $this->buffer .= $line . "\n";
    }

    public function emptyLine(): void
    {
        $this->buffer .= "\n";
    }

    public function lineBreak(): void
    {
        // noop
    }

    public function writeTitle(string $title): void
    {
        // noop
    }

    public function fetch(): string
    {
        return $this->buffer;
    }
}
