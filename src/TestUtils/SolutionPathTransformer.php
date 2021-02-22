<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\TestUtils;

class SolutionPathTransformer
{
    public static function tempPathToSolutionTempPath(string $tmpFilePath): string
    {
        $tmpDir = realpath(sys_get_temp_dir());

        if (!$tmpDir) {
            throw new \RuntimeException();
        }

        $file = str_replace($tmpDir, '', $tmpFilePath);

        return sprintf('%s/php-school/%s', $tmpDir, ltrim($file, '/'));
    }
}
