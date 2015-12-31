<?php

namespace PhpSchool\PhpWorkshop\Solution;

use InvalidArgumentException;

/**
 * Class SingleFileSolution
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class SingleFileSolution implements SolutionInterface
{

    /**
     * @var SolutionFile
     */
    private $file;

    /**
     * @param string $file
     * @throws InvalidArgumentException
     */
    public function __construct($file)
    {
        $this->file = SolutionFile::fromFile(realpath($file));
    }

    /**
     * @param string $file
     * @return static
     */
    public static function fromFile($file)
    {
        return new static($file);
    }
    
    /**
     * @return string
     */
    public function getEntryPoint()
    {
        return $this->file->__toString();
    }

    /**
     * @return string[]
     */
    public function getFiles()
    {
        return [$this->file];
    }

    /**
     * @return string
     */
    public function getBaseDirectory()
    {
        return $this->file->getBaseDirectory();
    }

    /**
     * @return bool
     */
    public function hasComposerFile()
    {
        return false;
    }
}
