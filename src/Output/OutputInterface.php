<?php

namespace PhpSchool\PhpWorkshop\Output;

use Psr\Http\Message\RequestInterface;

interface OutputInterface
{
    /**
     * Write a string as an error. Should be decorated in someway
     * which highlights the severity.
     *
     * @param string $error
     */
    public function printError($error);

    /**
     * Write a string to the output.
     *
     * @param string $content
     */
    public function write($content);

    /**
     * Write an array of strings, each on a new line.
     *
     * @param array $lines
     */
    public function writeLines(array $lines);

    /**
     * Write a string terminated with a newline.
     *
     * @param string $line
     */
    public function writeLine($line);

    /**
     * Write an empty line.
     */
    public function emptyLine();

    /**
     * Write a line break.
     *
     * @return string
     */
    public function lineBreak();

    /**
     * Write a title section. Should be decorated in a way which makes
     * the title stand out.
     *
     * @param string $title
     */
    public function writeTitle($title);
}
