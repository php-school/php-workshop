<?php

namespace PhpSchool\PhpWorkshopTest\Output;

use Colors\Color;
use PhpSchool\Terminal\Terminal;
use PHPUnit\Framework\TestCase;
use PhpSchool\PhpWorkshop\Output\StdOutput;

class StdOutputTest extends TestCase
{
    /**
     * @var Color
     */
    private $color;

    /**
     * @var StdOutput
     */
    private $output;

    public function setUp(): void
    {
        $this->color = new Color();
        $this->color->setForceStyle(true);
        $this->output = new StdOutput($this->color, $this->createMock(Terminal::class));
    }

    public function testPrintError(): void
    {
        $error  = "\n";
        $error .= " [41m       [0m\n";
        $error .= " [1m[97m[41m ERROR [0m[0m[0m\n";
        $error .= " [41m       [0m\n";
        $error .= "\n";

        $this->expectOutputString($error);

        $this->output->printError('ERROR');
    }

    public function testWrite(): void
    {
        $message  = 'There are people who actually like programming. ';
        $message .= "I don't understand why they like programming.";

        $this->expectOutputString($message);
        $this->output->write($message);
    }

    public function testWriteLine(): void
    {
        $message = 'Talk is cheap. Show me the code.';
        $this->expectOutputString($message . "\n");
        $this->output->writeLine($message);
    }

    public function testWriteLines(): void
    {
        $lines = ['Line 1', 'Line 2', 'Line 3'];
        $this->expectOutputString("Line 1\nLine 2\nLine 3\n");
        $this->output->writeLines($lines);
    }

    public function testEmptyLine(): void
    {
        $this->expectOutputString("\n");
        $this->output->emptyLine();
    }
}
