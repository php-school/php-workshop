<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Solution;

use InvalidArgumentException;

/**
 * Solution to use when the solution only consists of one file.
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
    public function __construct(string $file)
    {
        if (!file_exists($file)) {
            throw new InvalidArgumentException(sprintf('File: "%s" does not exist', $file));
        }

        $this->file = SolutionFile::fromFile((string) realpath($file));
    }

    /**
     * Static constructor to build an instance from an absolute file path.
     *
     * @param string $file The absolute path of the reference solution.
     * @return self
     * @throws InvalidArgumentException If the file does not exist.
     */
    public static function fromFile(string $file): self
    {
        return new self($file);
    }

    /**
     * Get the entry point. This is the PHP file that php would execute in order to run the
     * program.
     *
     * @return SolutionFile
     */
    public function getEntryPoint(): SolutionFile
    {
        return $this->file;
    }

    /**
     * Get all the files which are contained with the solution.
     *
     * @return array<SolutionFile>
     */
    public function getFiles(): array
    {
        return [$this->file];
    }

    /**
     * Get the absolute path to the directory containing the solution.
     *
     * @return string
     */
    public function getBaseDirectory(): string
    {
        return $this->file->getBaseDirectory();
    }

    /**
     * Single file solutions can never have composer files as there must be at least
     * one `php` file.
     *
     * @return bool
     */
    public function hasComposerFile(): bool
    {
        return false;
    }
}
