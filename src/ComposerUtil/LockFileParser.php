<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\ComposerUtil;

use PhpSchool\PhpWorkshop\Exception\InvalidArgumentException;

/**
 * Utility for reading installed package versions from a `composer.lock` file.
 */
class LockFileParser
{
    /**
     * @var array{packages: array<array{name: string, version: string}>}
     */
    private $contents;

    /**
     * @param string $lockFilePath The absolute path to the `composer.lock` file.
     * @throws InvalidArgumentException If the file does not exist.
     */
    public function __construct(string $lockFilePath)
    {
        if (!file_exists($lockFilePath)) {
            throw new InvalidArgumentException(sprintf('Lock File: "%s" does not exist', $lockFilePath));
        }

        $content = json_decode((string) file_get_contents($lockFilePath), true);

        if (!is_array($content)) {
            throw new InvalidArgumentException(sprintf('Lock File: "%s" is corrupted', $lockFilePath));
        }

        if (!isset($content['packages']) || !is_array($content['packages'])) {
            throw new InvalidArgumentException(sprintf('Lock File: "%s" does not contain packages key', $lockFilePath));
        }

        /** @var array{packages: array<array{name: string, version: string}>} $content */
        $this->contents = $content;
    }

    /**
     * Get an array of installed packages from the `composer.lock` file including their versions.
     *
     * ```php
     * [
     *     ['name' => 'my/package', 'version' => '1.0.0'],
     *     ['name' => 'my/second-package', 'version' => '1.1.0'],
     * ];
     * ```
     *
     * @return array<array{name: string, version: string}>
     */
    public function getInstalledPackages(): array
    {
        return array_map(function (array $packageDetails) {
            return [
                'name'      => $packageDetails['name'],
                'version'   => $packageDetails['version'],
            ];
        }, $this->contents['packages']);
    }

    /**
     * Check if a package name has been installed in any version.
     *
     * @param string $packageName
     * @return bool
     */
    public function hasInstalledPackage(string $packageName): bool
    {
        foreach ($this->contents['packages'] as $packageDetails) {
            if ($packageName === $packageDetails['name']) {
                return true;
            }
        }

        return false;
    }
}
