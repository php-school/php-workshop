<?php

namespace PhpSchool\PhpWorkshop\Output;

use Colors\Color;
use PhpSchool\Terminal\Terminal;

/**
 * Class StdOutput
 * @package PhpSchool\PhpWorkshop
 * @author Aydin Hassan <aydin@hotmail.co.uk>
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
    public function printError($error)
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
    public function writeTitle($title)
    {
        echo sprintf("\n%s\n", $this->color->__invoke($title)->underline()->bold());
    }

    /**
     * Write a string to the output.
     *
     * @param string $content
     */
    public function write($content)
    {
        echo $content;
    }

    /**
     * Write an array of strings, each on a new line.
     *
     * @param array $lines
     */
    public function writeLines(array $lines)
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
    public function writeLine($line)
    {
        echo sprintf("%s\n", $line);
    }

    /**
     * Write an empty line.
     */
    public function emptyLine()
    {
        echo "\n";
    }

    /**
     * Write a line break.
     *
     * @return string
     */
    public function lineBreak()
    {
        echo $this->color->__invoke(str_repeat('─', $this->terminal->getWidth()))->yellow();
    }
}
