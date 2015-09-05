<?php

namespace PhpWorkshop\PhpWorkshop\Comparator;
use PhpWorkshop\PhpWorkshop\Exercise\ExerciseInterface;
use PhpWorkshop\PhpWorkshop\Fail;
use Success;

/**
 * Class ComparatorInterface
 * @package PhpWorkshop\PhpWorkshop\Comparator
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */

interface ComparatorInterface
{
    /**
     * @param ExerciseInterface $exercise
     * @param string $fileName
     * @return Fail|Success
     */
    public function compare(ExerciseInterface $exercise, $fileName);
}