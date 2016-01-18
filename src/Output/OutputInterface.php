<?php

namespace PhpSchool\PhpWorkshop\Output;

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
}
