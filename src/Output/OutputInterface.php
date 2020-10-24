<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Output;

/**
 * Output interface
 */
interface OutputInterface
{
    /**
     * Write a string as an error. Should be decorated in someway
     * which highlights the severity.
     *
     * @param string $error
     */
    public function printError(string $error): void;

    /**
     * Write a string to the output.
     *
     * @param string $content
     */
    public function write(string $content): void;

    /**
     * Write an array of strings, each on a new line.
     *
     * @param array<string> $lines
     */
    public function writeLines(array $lines): void;

    /**
     * Write a string terminated with a newline.
     *
     * @param string $line
     */
    public function writeLine(string $line): void;

    /**
     * Write an empty line.
     */
    public function emptyLine(): void;

    /**
     * Write a line break.
     */
    public function lineBreak(): void;

    /**
     * Write a title section. Should be decorated in a way which makes
     * the title stand out.
     *
     * @param string $title
     */
    public function writeTitle(string $title): void;
}
