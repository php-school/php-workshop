<?php

namespace PhpSchool\PhpWorkshop\Solution;

use InvalidArgumentException;
use RecursiveCallbackFilterIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Solution which can contain multiple files, the file to execute is defined as the entry point.
 */
class DirectorySolution implements SolutionInterface
{
    /**
     * @var string
     */
    private $entryPoint;

    /**
     * @var array<SolutionFile>
     */
    private $files = [];

    /**
     * @var string
     */
    private $baseDirectory;

    /**
     * Build an instance from a given directory. Requires a file name to be used as the entry point,
     * and optionally can take an array of files to exclude from the solution. For example you may want to
     * ignore some dot files or `composer.lock`.
     *
     * @param string $directory The directory to search for files.
     * @param string $entryPoint The relative path from the directory of the entry point file.
     * @param array<string> $exclusions An array of file names to exclude from the folder.
     * @throws InvalidArgumentException If the entry point does not exist in the folder.
     */
    public function __construct(string $directory, string $entryPoint, array $exclusions = [])
    {
        $directory  = (string) realpath(rtrim($directory, '/'));
        $entryPoint = ltrim($entryPoint, '/');

        $dir  = new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS);
        $iter = new RecursiveIteratorIterator(
            new RecursiveCallbackFilterIterator($dir, function (\SplFileInfo $current) use ($exclusions) {
                return !in_array($current->getBasename(), $exclusions, true);
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
     * Static constructor to build an instance from a directory.
     *
     * @param string $directory The directory to search for files.
     * @param array<string> $exclusions An array of file names to exclude from the folder.
     * @param string $entryPoint The relative path from the directory of the entry point file.
     * @return self
     */
    public static function fromDirectory(string $directory, array $exclusions = [], $entryPoint = 'solution.php'): self
    {
        return new self($directory, $entryPoint, array_merge($exclusions, ['composer.lock', 'vendor']));
    }

    /**
     * Get the entry point. This is the PHP file that PHO would execute in order to run the
     * program. This should be the absolute path.
     *
     * @return string
     */
    public function getEntryPoint(): string
    {
        return $this->entryPoint;
    }

    /**
     * Get all the files which are contained with the solution.
     *
     * @return array<SolutionFile>
     */
    public function getFiles(): array
    {
        return $this->files;
    }

    /**
     * Get the absolute path to the directory containing the solution.
     *
     * @return string
     */
    public function getBaseDirectory(): string
    {
        return $this->baseDirectory;
    }

    /**
     * Check whether there is a `composer.lock` file in the base directory.
     *
     * @return bool
     */
    public function hasComposerFile(): bool
    {
        return file_exists(sprintf('%s/composer.lock', $this->baseDirectory));
    }
}
