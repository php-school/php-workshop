<?php

namespace PhpWorkshop\PhpWorkshop\Check;

use PhpWorkshop\PhpWorkshop\Exercise\ExerciseInterface;
use PhpWorkshop\PhpWorkshop\Fail;
use PhpWorkshop\PhpWorkshop\Success;

/**
 * Class CheckInterface
 * @package PhpWorkshop\PhpWorkshop\Comparator
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */

interface CheckInterface
{
    /**
     * @param ExerciseInterface $exercise
     * @param string $fileName
     * @return Fail|Success
     */
    public function check(ExerciseInterface $exercise, $fileName);
}