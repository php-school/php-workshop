<?php

namespace PhpSchool\PhpWorkshop\Exercise;

/**
 * Class TemporaryDirectoryTrait
 * @package PhpSchool\PhpWorkshop\Exercise
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
trait TemporaryDirectoryTrait
{
    /**
     * @return string
     */
    protected function getTemporaryPath()
    {
        return sprintf(
            '%s/%s',
            str_replace('\\', '/', realpath(sys_get_temp_dir())),
            str_replace('\\', '_', __CLASS__)
        );
    }
}
