<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Solution;

use Symfony\Component\Filesystem\Filesystem;

class InMemorySolution implements SolutionInterface
{
    /**
     * @var string
     */
    private string $baseDirectory;

    /**
     * @var SolutionFile[]
     */
    private array $files;

    private function __construct(SolutionInterface $solution)
    {
        $fileSystem = new Filesystem();

        $currentPath = explode('/', realpath(__DIR__));
        $solutionPath = explode('/', realpath($solution->getBaseDirectory()));
        $entryPointPath = explode('/', realpath($solution->getEntryPoint()));

        $intersection = array_intersect($currentPath, $solutionPath);

        $basename = implode('/', array_diff($solutionPath, $intersection));
        $entrypoint = implode('/', array_diff($entryPointPath, $intersection));

        $this->baseDirectory = sprintf('%s/php-school/%s', sys_get_temp_dir(), $basename);
        $this->entryPoint = sprintf('%s/php-school/%s', sys_get_temp_dir(), $entrypoint);

        if ($fileSystem->exists($this->baseDirectory)) {
            $fileSystem->remove($this->baseDirectory);
        }

        $fileSystem->mkdir($this->baseDirectory);

        $dirIterator = new \RecursiveDirectoryIterator(
            $solution->getBaseDirectory(),
            \RecursiveDirectoryIterator::SKIP_DOTS
        );
        $iterator = new \RecursiveIteratorIterator($dirIterator, \RecursiveIteratorIterator::SELF_FIRST);

        foreach ($iterator as $file) {
            $target = sprintf('%s/%s', $this->baseDirectory, $iterator->getSubPathName());
            $file->isDir()
                ? $fileSystem->mkdir($target)
                : $fileSystem->copy($file->getPathname(), $target);
        }

        $this->files = array_map(function (SolutionFile $solutionFile) use ($intersection) {
            $filePath = explode('/', realpath($solutionFile->__toString()));
            $file = implode('/', array_diff($filePath, $intersection));
            return SolutionFile::fromFile(sprintf('%s/php-school/%s', sys_get_temp_dir(), $file));
        }, $solution->getFiles());
    }

    public static function fromSolution(SolutionInterface $solution)
    {
        return new self($solution);
    }

    /**
     * Get the entry point. This is the PHP file that PHP would execute in order to run the
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
