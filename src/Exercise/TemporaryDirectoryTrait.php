<?php

namespace PhpSchool\PhpWorkshop\Exercise;

/**
 * Helper trait to use in exercises to get a temporary path
 * for IO stuff.
 *
 * @package PhpSchool\PhpWorkshop\Exercise
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
trait TemporaryDirectoryTrait
{
    /**
     * Get a temporary directory to use in exercises, takes in to account
     * the class-name.
     *
     * @return string The absolute path to the temporary directory.
     */
    public function getTemporaryPath()
    {
        return sprintf(
            '%s/%s',
            str_replace('\\', '/', realpath(sys_get_temp_dir())),
            str_replace('\\', '_', __CLASS__)
        );
    }
}
