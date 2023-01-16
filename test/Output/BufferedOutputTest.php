<?php

namespace PhpSchool\PhpWorkshopTest\Output;

use PhpSchool\PhpWorkshop\Output\BufferedOutput;
use PHPUnit\Framework\TestCase;

class BufferedOutputTest extends TestCase
{
    /**
     * @var BufferedOutput
     */
    private $output;

    public function setUp(): void
    {
        $this->output = new BufferedOutput();
    }

    public function testPrintErrorDoesNothing(): void
    {
        $this->output->printError('Some Error');
        $this->assertEmpty($this->output->fetch());
    }

    public function testPrintExceptionDoesNothing(): void
    {
        $this->output->printException(new \ErrorException());
        $this->assertEmpty($this->output->fetch());
    }

    public function testLineBreakDoesNothing(): void
    {
        $this->output->lineBreak();
        $this->assertEmpty($this->output->fetch());
    }

    public function testWriteTitleDoesNothing(): void
    {
        $this->output->writeTitle('Some Title');
        $this->assertEmpty($this->output->fetch());
    }

    public function testWrite(): void
    {
        $message  = 'There are people who actually like programming. ';
        $message .= "I don't understand why they like programming.";

        $this->output->write($message);
        $this->assertEquals($message, $this->output->fetch());
    }

    public function testWriteLine(): void
    {
        $message = 'Talk is cheap. Show me the code.';
        $this->output->writeLine($message);
        $this->assertEquals($message . "\n", $this->output->fetch());
        $this->assertEquals($message . "\n", $this->output->fetch());
    }

    public function testWriteLines(): void
    {
        $lines = ['Line 1', 'Line 2', 'Line 3'];
        $this->output->writeLines($lines);
        $this->assertEquals("Line 1\nLine 2\nLine 3\n", $this->output->fetch());
    }

    public function testEmptyLine(): void
    {
        $this->output->emptyLine();
        $this->assertEquals("\n", $this->output->fetch());
    }

    public function testFetchWithClear(): void
    {
        $message = 'Talk is cheap. Show me the code.';
        $this->output->writeLine($message);
        $this->assertEquals($message . "\n", $this->output->fetch(true));
        $this->assertEquals('', $this->output->fetch());
    }
}
