<?php

namespace PhpWorkshop\PhpWorkshopTest\Asset;

use PhpWorkshop\PhpWorkshop\Exercise\ExerciseInterface;
use PhpWorkshop\PhpWorkshop\ExerciseCheck\StdOutExerciseCheck;

/**
 * Class StdOutExercise
 * @package PhpWorkshop\PhpWorkshopTest
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class StdOutExercise implements ExerciseInterface, StdOutExerciseCheck
{

    /**
     * @return string
     */
    public function getName()
    {
        // TODO: Implement getName() method.
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        // TODO: Implement getDescription() method.
    }

    /**
     * @return string
     */
    public function getSolution()
    {
        // TODO: Implement getSolution() method.
    }

    /**
     * @return string
     */
    public function getProblem()
    {
        // TODO: Implement getProblem() method.
    }

    /**
     * @return array
     */
    public function getArgs()
    {
        // TODO: Implement getArgs() method.
    }

    /**
     * @return void
     */
    public function tearDown()
    {
        // TODO: Implement tearDown() method.
    }
}
