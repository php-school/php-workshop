<?php

namespace PhpSchool\PhpWorkshop\Output;

use Colors\Color;
use PhpSchool\CliMenu\Terminal\TerminalInterface;
use Psr\Http\Message\RequestInterface;
use Zend\Diactoros\Request;

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
     * @var TerminalInterface
     */
    private $terminal;

    /**
     * @param Color             $color
     * @param TerminalInterface $terminal
     */
    public function __construct(Color $color, TerminalInterface $terminal)
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

    /**
     * Write a PSR-7 request.
     *
     * @param RequestInterface $request
     */
    public function writeRequest(RequestInterface $request)
    {
        echo sprintf("URL:     %s\n", $request->getUri());
        echo sprintf("METHOD:  %s\n", $request->getMethod());

        if ($request->getHeaders()) {
            echo 'HEADERS:';
        }

        $indent = false;
        foreach ($request->getHeaders() as $name => $values) {
            if ($indent) {
                echo str_repeat(' ', 9);
            }

            echo sprintf(" %s: %s\n", $name, implode(', ', $values));
            $indent  = true;
        }

        if ($body = (string) $request->getBody()) {
            echo "\nBODY:";

            switch ($request->getHeaderLine('Content-Type')) {
                case 'application/json':
                    echo json_encode(json_decode($body, true), JSON_PRETTY_PRINT);
                    break;
                default:
                    echo $body;
                    break;
            }

            $this->emptyLine();
        }
    }
}
