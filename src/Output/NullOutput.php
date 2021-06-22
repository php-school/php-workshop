<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Output;

use Throwable;

class NullOutput implements OutputInterface
{
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
        // noop
    }

    public function writeLines(array $lines): void
    {
        // noop
    }

    public function writeLine(string $line): void
    {
        // noop
    }

    public function emptyLine(): void
    {
        // noop
    }

    public function lineBreak(): void
    {
        // noop
    }

    public function writeTitle(string $title): void
    {
        // noop
    }
}
