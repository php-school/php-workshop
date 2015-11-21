<?php

namespace PhpSchool\PhpWorkshop\Exercise;

use PhpSchool\PhpWorkshop\CodeModification;

/**
 * Interface PreProcessable
 * @package PhpSchool\PhpWorkshop\Exercise
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
interface PreProcessable
{
    /**
     * @return CodeModification[]
     */
    public function getModifications();
}
