<?php

namespace PhpSchool\PhpWorkshop\Check;

/**
 * Class CheckInterface
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
     * when requiring this check
     *
     * @return string
     */
    public function getExerciseInterface();
}
