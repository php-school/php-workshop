<?php

namespace PhpSchool\PhpWorkshop\ComposerUtil;

use PhpSchool\PhpWorkshop\Exception\InvalidArgumentException;

/**
 * Utility for reading installed package versions from a `composer.lock` file.
 */
class LockFileParser
{
    /**
     * @var string
     */
    private $contents;

    /**
     * @param string $lockFilePath The absolute path to the `composer.lock` file.
     * @throws InvalidArgumentException If the file does not exist.
     */
    public function __construct($lockFilePath)
    {
        if (!file_exists($lockFilePath)) {
            throw new InvalidArgumentException(sprintf('Lock File: "%s" does not exist', $lockFilePath));
        }
        
        $this->contents = json_decode(file_get_contents($lockFilePath), true);
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
     * @return array
     */
    public function getInstalledPackages()
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
    public function hasInstalledPackage($packageName)
    {
        foreach ($this->contents['packages'] as $packageDetails) {
            if ($packageName === $packageDetails['name']) {
                return true;
            }
        }
        
        return false;
    }
}
