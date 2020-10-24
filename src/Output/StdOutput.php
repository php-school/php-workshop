<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Output;

use Colors\Color;
use PhpSchool\Terminal\Terminal;

/**
 * Console output interface
 */
class StdOutput implements OutputInterface
{
    /**
     * @var \Colors\Color
     */
    private $color;

    /**
     * @var Terminal
     */
    private $terminal;

    public function __construct(Color $color, Terminal $terminal)
    {
        $this->color = $color;
        $this->terminal = $terminal;
    }

    /**
     * @param string $error
     */
    public function printError(string $error): void
    {
        $length = strlen($error) + 2;
        echo "\n";
        echo sprintf(" %s\n", $this->color->__invoke(str_repeat(' ', $length))->bg_red());
        echo sprintf(" %s\n", $this->color->__invoke(sprintf(' %s ', $error))->bg_red()->white()->bold());
        echo sprintf(" %s\n", $this->color->__invoke(str_repeat(' ', $length))->bg_red());
        echo "\n";
    }

    /**
     * Write a title section. Should be decorated in a way which makes
     * the title stand out.
     *
     * @param string $title
     */
    public function writeTitle(string $title): void
    {
        echo sprintf("\n%s\n", $this->color->__invoke($title)->underline()->bold());
    }

    /**
     * Write a string to the output.
     *
     * @param string $content
     */
    public function write(string $content): void
    {
        echo $content;
    }

    /**
     * Write an array of strings, each on a new line.
     *
     * @param array<string> $lines
     */
    public function writeLines(array $lines): void
    {
        foreach ($lines as $line) {
            $this->writeLine($line);
        }
    }

    /**
     * Write a string terminated with a newline.
     *
     * @param string $line
     */
    public function writeLine(string $line): void
    {
        echo sprintf("%s\n", $line);
    }

    /**
     * Write an empty line.
     */
    public function emptyLine(): void
    {
        echo "\n";
    }

    /**
     * Write a line break.
     */
    public function lineBreak(): void
    {
        echo $this->color->__invoke(str_repeat('─', $this->terminal->getWidth()))->yellow();
    }
}
