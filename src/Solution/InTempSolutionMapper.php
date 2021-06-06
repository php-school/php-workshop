<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Solution;

use PhpSchool\PhpWorkshop\Utils\Path;
use PhpSchool\PhpWorkshop\Utils\System;
use Symfony\Component\Filesystem\Filesystem;

class InTempSolutionMapper
{
    public static function mapDirectory(string $directory): string
    {
        $fileSystem = new Filesystem();
        $tempDir = self::getDeterministicTempDir($directory);

        $fileSystem->mkdir($tempDir);

        $dirIterator = new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS);
        $iterator = new \RecursiveIteratorIterator($dirIterator, \RecursiveIteratorIterator::SELF_FIRST);

        foreach ($iterator as $file) {
            $target = Path::join($tempDir, $iterator->getSubPathName());

            if ($fileSystem->exists($target)) {
                continue;
            }

            $file->isDir()
                ? $fileSystem->mkdir($target)
                : $fileSystem->copy($file->getPathname(), $target);
        }

        return $tempDir;
    }

    public static function mapFile(string $file): string
    {
        $fileSystem = new Filesystem();
        $tempFile = Path::join(self::getDeterministicTempDir($file), basename($file));

        if ($fileSystem->exists($tempFile)) {
            return $tempFile;
        }

        $fileSystem->mkdir(System::tempDir());
        $fileSystem->copy($file, $tempFile);

        return $tempFile;
    }

    private static function getDeterministicTempDir(string $path): string
    {
        return Path::join(System::tempDir(), md5($path));
    }
}
