<?php

namespace PhpSchool\PhpWorkshop\Check;

/**
 * Base Interface for Checks.
 *
 * @package PhpSchool\PhpWorkshop\Comparator
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
interface CheckInterface
{
    /**
     * Return the check's name
     *
     * @return string
     */
    public function getName();

    /**
     * This returns the interface the exercise should implement
     * when requiring this check. It should be the FQCN of the interface.
     *
     * @return string
     */
    public function getExerciseInterface();
}
