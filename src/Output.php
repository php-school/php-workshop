<?php

namespace PhpWorkshop\PhpWorkshop;

use Colors\Color;

/**
 * Class Output
 * @package PhpWorkshop\PhpWorkshop
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
        $length = strlen($error) + 2;
        echo "\n";
        echo sprintf(" %s\n", $this->color->__invoke(str_repeat(' ', $length))->bg_red());
        echo sprintf(" %s\n", $this->color->__invoke(sprintf(" %s ", $error))->bg_red()->white()->bold());
        echo sprintf(" %s\n", $this->color->__invoke(str_repeat(' ', $length))->bg_red());
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
     * @param string $line
     */
    public function writeLine($line)
    {
        echo sprintf("%s\n", $line);
    }
}
