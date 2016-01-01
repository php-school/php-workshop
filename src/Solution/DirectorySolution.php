<?php

namespace PhpSchool\PhpWorkshop\Solution;

use InvalidArgumentException;

/**
 * Class DirectorySolution
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class DirectorySolution implements SolutionInterface
{

    /**
     * @var string
     */
    private $entryPoint;

    /**
     * @var SolutionFile[]
     */
    private $files = [];

    /**
     * @var string
     */
    private $baseDirectory;

    /**
     * @param string $directory
     * @param string $entryPoint
     * @throws InvalidArgumentException
     */
    public function __construct($directory, $entryPoint)
    {
        $directory  = realpath(rtrim($directory, '/'));
        $entryPoint = ltrim($entryPoint, '/');
        
        $files = array_values(array_diff(scandir($directory), ['..', '.']));
        sort($files);
        
        if (!in_array($entryPoint, $files)) {
            throw new InvalidArgumentException(
                sprintf('Entry point: "%s" does not exist in: "%s"', $entryPoint, $directory)
            );
        }
        
        $this->files = array_map(function ($file) use ($directory) {
            return new SolutionFile($file, $directory);
        }, $files);
        
        $this->entryPoint = sprintf('%s/%s', $directory, $entryPoint);
        $this->baseDirectory = $directory;
    }

    /**
     * @param string $directory
     * @param string $entryPoint
     * @return static
     */
    public static function fromDirectory($directory, $entryPoint = 'solution.php')
    {
        return new static($directory, $entryPoint);
    }
    
    /**
     * @return string
     */
    public function getEntryPoint()
    {
        return $this->entryPoint;
    }

    /**
     * @return string[]
     */
    public function getFiles()
    {
        return $this->files;
    }

    /**
     * @return string
     */
    public function getBaseDirectory()
    {
        return $this->baseDirectory;
    }

    /**
     * @return bool
     */
    public function hasComposerFile()
    {
        return file_exists(sprintf('%s/composer.lock', $this->baseDirectory));
    }
}
