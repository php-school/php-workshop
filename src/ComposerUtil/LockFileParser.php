<?php

namespace PhpSchool\PhpWorkshop\ComposerUtil;

use PhpSchool\PhpWorkshop\Exception\InvalidArgumentException;

/**
 * Class LockFileParser
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class LockFileParser
{
    /**
     * @var string
     */
    private $contents;

    /**
     * @param string $lockFilePath
     * @throws InvalidArgumentException
     */
    public function __construct($lockFilePath)
    {
        if (!file_exists($lockFilePath)) {
            throw new InvalidArgumentException(sprintf('Lock File: "%s" does not exist', $lockFilePath));
        }
        
        $this->contents = json_decode(file_get_contents($lockFilePath), true);
    }

    /**
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
