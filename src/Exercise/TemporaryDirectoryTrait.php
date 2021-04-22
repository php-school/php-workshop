<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Exercise;

use PhpSchool\PhpWorkshop\Utils\System;

/**
 * Helper trait to use in exercises to get a temporary path
 * for IO stuff.
 */
trait TemporaryDirectoryTrait
{
    /**
     * Get a temporary directory to use in exercises, takes in to account
     * the class-name.
     *
     * @return string The absolute path to the temporary directory.
     */
    public function getTemporaryPath(): string
    {
        return System::tempDir(str_replace('\\', '_', __CLASS__));
    }
}
