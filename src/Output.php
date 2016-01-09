<?php

namespace PhpSchool\PhpWorkshop;

use Colors\Color;

/**
 * Class Output
 * @package PhpSchool\PhpWorkshop
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class Output
{
    /**
     * @var \Colors\Color
     */
    private $color;

    /**
     * @param Color $color
     */
    public function __construct(Color $color)
    {
        $this->color = $color;
    }

    /**
     * @param string $error
     */
    public function printError($error)
    {
        $this->printAlert($error, 'red', 'white');
    }

    /**
     * @param $success
     */
    public function printSuccess($success)
    {
        $this->printAlert($success, 'green', 'white');
    }

    /**
     * @param $info
     */
    public function printInfo($info)
    {
        $this->printAlert($info, 'blue', 'white');
    }

    /**
     * Print text with coloured background and padding
     *
     * @param $alert
     * @param $bgColor
     */
    private function printAlert($alert, $bgColor, $fgColor)
    {
        $length = mb_strlen($alert) + 2;
        echo "\n";
        echo sprintf(" %s\n", $this->color->__invoke(str_repeat(' ', $length))->bg($bgColor));
        echo sprintf(
            " %s\n",
            $this->color->__invoke(sprintf(" %s ", $alert))->bg($bgColor)->fg($fgColor)->bold()
        );
        echo sprintf(" %s\n", $this->color->__invoke(str_repeat(' ', $length))->bg($bgColor));
        echo "\n";
    }

    /**
     * @param string $content
     */
    public function write($content)
    {
        echo $content;
    }

    /**
     * @param array $lines
     */
    public function writeLines(array $lines)
    {
        foreach ($lines as $line) {
            $this->writeLine($line);
        }
    }

    /**
     * @param string $line
     */
    public function writeLine($line)
    {
        echo sprintf("%s\n", $line);
    }

    /**
     * Write empty line
     */
    public function emptyLine()
    {
        echo "\n";
    }
}
