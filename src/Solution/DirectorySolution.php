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
     * @param string $directory
     * @param string $entryPoint
     * @throws InvalidArgumentException
     */
    public function __construct($directory, $entryPoint)
    {
        $directory  = rtrim($directory, '/');
        $entryPoint = ltrim($entryPoint, '/');
        
        $files = array_values(array_diff(scandir($directory), ['..', '.']));
        
        if (!in_array($entryPoint, $files)) {
            throw new InvalidArgumentException(
                sprintf('Entry point: "%s" does not exist in: "%s"', $entryPoint, $directory)
            );
        }
        
        $this->files = array_map(function ($file) use ($directory) {
            return new SolutionFile($file, $directory);
        }, $files);
        
        $this->entryPoint = sprintf('%s/%s', $directory, $entryPoint);
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
}
