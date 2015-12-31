<?php

namespace PhpSchool\PhpWorkshop\Solution;

use InvalidArgumentException;

/**
 * Class SolutionFile
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
     * @param string $relativePath
     * @param string $baseDirectory
     * @throws InvalidArgumentException
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
     * @param string $file
     * @return static
     */
    public static function fromFile($file)
    {
        return new static(basename($file), dirname($file));
    }

    /**
     * @return string
     */
    private function getAbsolutePath()
    {
        return sprintf('%s/%s', $this->baseDirectory, $this->relativePath);
    }

    /**
     * @return string
     */
    public function getRelativePath()
    {
        return $this->relativePath;
    }

    /**
     * @return string
     */
    public function getBaseDirectory()
    {
        return $this->baseDirectory;
    }

    /**
     * @return string
     */
    public function getContents()
    {
        return file_get_contents($this->getAbsolutePath());
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getAbsolutePath();
    }
}
