<?php

namespace PhpSchool\PhpWorkshop\Solution;

use InvalidArgumentException;

/**
 * This class represents a file on the file system which is part of the reference solution.
 *
 * @package PhpSchool\PhpWorkshop\Solution
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class SolutionFile
{
    /**
     * @var string
     */
    private $relativePath;

    /**
     * @var string
     */
    private $baseDirectory;

    /**
     * @param string $relativePath The relative path from the base directory.
     * @param string $baseDirectory The base directory of the solution.
     * @throws InvalidArgumentException If the file does not exist.
     */
    public function __construct($relativePath, $baseDirectory)
    {
        $this->relativePath  = trim($relativePath, '/');
        $this->baseDirectory = rtrim($baseDirectory, '/');

        if (!file_exists($file = $this->getAbsolutePath())) {
            throw new InvalidArgumentException(sprintf('File: "%s" does not exist', $file));
        }
    }

    /**
     * Static constructor to create an instance from a file path.
     * Will assume the base directory should be the immediate parent of the file.
     *
     * @param string $file
     * @return static
     */
    public static function fromFile($file)
    {
        return new static(basename($file), dirname($file));
    }

    /**
     * Get the absolute path of the file.
     *
     * @return string
     */
    private function getAbsolutePath()
    {
        return sprintf('%s/%s', $this->baseDirectory, $this->relativePath);
    }

    /**
     * Get the relative path of the file from the base directory.
     *
     * @return string
     */
    public function getRelativePath()
    {
        return $this->relativePath;
    }

    /**
     * Get the base directory of the file.
     *
     * @return string
     */
    public function getBaseDirectory()
    {
        return $this->baseDirectory;
    }

    /**
     * Get the contents of the file.
     *
     * @return string
     */
    public function getContents()
    {
        return file_get_contents($this->getAbsolutePath());
    }

    /**
     * Proxy to the absolute path.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getAbsolutePath();
    }
}
