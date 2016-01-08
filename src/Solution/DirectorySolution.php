<?php

namespace PhpSchool\PhpWorkshop\Solution;

use InvalidArgumentException;
use RecursiveCallbackFilterIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Class DirectorySolution
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 * @author Michael Woodward <mikeymike.mw@gmail.com>
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
     * @param array  $exclusions
     * @throws InvalidArgumentException
     */
    public function __construct($directory, $entryPoint, array $exclusions = [])
    {
        $directory  = realpath(rtrim($directory, '/'));
        $entryPoint = ltrim($entryPoint, '/');

        $dir  = new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS);
        $iter = new RecursiveIteratorIterator(
            new RecursiveCallbackFilterIterator($dir, function (\SplFileInfo $current) use ($exclusions) {
                return !in_array($current->getBasename(), $exclusions);
            }),
            RecursiveIteratorIterator::SELF_FIRST
        );

        $files = [];
        foreach ($iter as $file) {
            if ($file->isFile()) {
                $files[] = trim(substr($file->getPathname(), strlen($directory)), '/');
            }
        }
        sort($files);

        if (!in_array($entryPoint, $files)) {
            throw new InvalidArgumentException(
                sprintf('Entry point: "%s" does not exist in: "%s"', $entryPoint, $directory)
            );
        }
        
        $this->files = array_map(function ($file) use ($directory) {
            return new SolutionFile($file, $directory);
        }, $files);
        
        $this->entryPoint    = sprintf('%s/%s', $directory, $entryPoint);
        $this->baseDirectory = $directory;
    }

    /**
     * @param string $directory
     * @param array  $exclusions
     * @param string $entryPoint
     * @return static
     */
    public static function fromDirectory($directory, array $exclusions = [], $entryPoint = 'solution.php')
    {
        return new static($directory, $entryPoint, array_merge($exclusions, ['composer.lock', 'vendor']));
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
