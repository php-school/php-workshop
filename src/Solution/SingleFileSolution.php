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
     * @param string $file The absolute path of the reference solution.
     * @throws InvalidArgumentException If the file does not exist.
     */
    public function __construct($file)
    {
        $this->file = SolutionFile::fromFile(realpath($file));
    }

    /**
     * Static constructor to build an instance from an absolute file path.
     *
     * @param string $file The absolute path of the reference solution.
     * @return static
     * @throws InvalidArgumentException If the file does not exist.
     */
    public static function fromFile($file)
    {
        return new static($file);
    }

    /**
     * Get the entry point. This is the PHP file that php would execute in order to run the
     * program. This should be the absolute path.
     *
     * @return string
     */
    public function getEntryPoint()
    {
        return $this->file->__toString();
    }

    /**
     * Get all the files which are contained with the solution.
     *
     * @return SolutionFile[]
     */
    public function getFiles()
    {
        return [$this->file];
    }

    /**
     * Get the absolute path to the directory containing the solution.
     *
     * @return string
     */
    public function getBaseDirectory()
    {
        return $this->file->getBaseDirectory();
    }

    /**
     * Single file solutions can never have composer files as there must be at least
     * one `php` file.
     *
     * @return bool
     */
    public function hasComposerFile()
    {
        return false;
    }
}
