<?php

namespace PhpSchool\PhpWorkshopTest;

use Colors\Color;
use PHPUnit_Framework_TestCase;
use PhpSchool\PhpWorkshop\Output;

/**
 * Class OutputTest
 * @package PhpSchool\PhpWorkshopTest
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class OutputTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Color
     */
    private $color;

    /**
     * @var Output
     */
    private $output;

    public function setUp()
    {
        $this->color = new Color();
        $this->color->setForceStyle(true);
        $this->output = new Output($this->color);
    }

    public function testPrintError()
    {
        $error  = "\n";
        $error .= " [41m       [0m\n";
        $error .= " [1m[97m[41m ERROR [0m[0m[0m\n";
        $error .= " [41m       [0m\n";
        $error .= "\n";

        $this->expectOutputString($error);

        $this->output->printError('ERROR');
    }

    public function testWrite()
    {
        $message  = "There are people who actually like programming. ";
        $message .= "I don't understand why they like programming.";

        $this->expectOutputString($message);
        $this->output->write($message);
    }

    public function testWriteLine()
    {
        $message = "Talk is cheap. Show me the code.";
        $this->expectOutputString($message . "\n");
        $this->output->writeLine($message);
    }
}
