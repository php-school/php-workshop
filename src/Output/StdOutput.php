<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Output;

use Colors\Color;
use PhpSchool\Terminal\Terminal;
use Throwable;

/**
 * Console output interface
 */
class StdOutput implements OutputInterface
{
    /**
     * @var Color
     */
    private $color;

    /**
     * @var Terminal
     */
    private $terminal;

    /**
     * @var string
     */
    private $workshopBasePath;

    public function __construct(Color $color, Terminal $terminal, string $workshopBasePath = '')
    {
        $this->color = $color;
        $this->terminal = $terminal;
        $this->workshopBasePath = $workshopBasePath;
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
     * @param Throwable $exception
     */
    public function printException(Throwable $exception): void
    {
        $message = $exception->getMessage();
        if (strpos($message, $this->workshopBasePath) !== null) {
            $message = str_replace($this->workshopBasePath, '', $message);
        }

        $file = $exception->getFile();
        if (strpos($file, $this->workshopBasePath) !== null) {
            $file = str_replace($this->workshopBasePath, '', $file);
        }

        $lines = [
            sprintf("In %s line %d:", $file, $exception->getLine()),
            sprintf("[%s (%s)]:", get_class($exception), $exception->getCode()),
            '',
            $message,
        ];

        $length = max(array_map('strlen', $lines)) + 2;
        $this->emptyLine();
        $this->writeLine(' ' . $this->color->__invoke(str_repeat(' ', $length))->bg_red());

        foreach ($lines as $line) {
            $line = str_pad($line, $length - 2, ' ', STR_PAD_RIGHT);
            $this->writeLine(' ' . $this->color->__invoke(" $line ")->bg_red()->white()->bold());
        }

        $this->writeLine(' ' . $this->color->__invoke(str_repeat(' ', $length))->bg_red());
        $this->emptyLine();

        $this->writeLine(
            implode(
                "\n",
                array_map(
                    function ($l) {
                        return " $l";
                    },
                    explode("\n", $exception->getTraceAsString()),
                ),
            ),
        );
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
