<?php

namespace PhpWorkshop\PhpWorkshop;

use PhpWorkshop\PhpWorkshop\Exercise\ExerciseInterface;

/**
 * Class Fail
 * @package PhpWorkshop\PhpWorkshop
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class Success
{
    /**
     * @var ExerciseInterface
     */
    private $exercise;

    /**
     * @param Exercise\ExerciseInterface $exercise
     */
    public function __construct(ExerciseInterface $exercise)
    {
        $this->exercise = $exercise;
    }
}