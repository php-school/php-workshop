<?php

namespace PhpSchool\PhpWorkshop\Output;

use Psr\Http\Message\RequestInterface;
use Zend\Diactoros\Request;

/**
 * Interface StdOutput
 * @package PhpSchool\PhpWorkshop
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
interface OutputInterface
{
    /**
     * @param string $error
     */
    public function printError($error);

    /**
     * @param string $content
     */
    public function write($content);

    /**
     * @param array $lines
     */
    public function writeLines(array $lines);

    /**
     * @param string $line
     */
    public function writeLine($line);

    /**
     * Write empty line
     */
    public function emptyLine();

    /**
     * @return string
     */
    public function lineBreak();

    /**
     * @param string $title
     */
    public function writeTitle($title);

    /**
     * @param RequestInterface $request
     */
    public function writeRequest(RequestInterface $request);
}
