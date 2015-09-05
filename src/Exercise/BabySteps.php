<?php

namespace PhpWorkshop\PhpWorkshop\Exercise;

use PhpWorkshop\PhpWorkshop\ExerciseCheck\StdOutExerciseCheck;

/**
 * Class BabySteps
 * @package PhpWorkshop\PhpWorkshop\Exercise
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class BabySteps implements ExerciseInterface, StdOutExerciseCheck
{

    /**
     * @return string
     */
    public function getName()
    {
        return 'Baby Steps';
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return 'NASA Level Programming';
    }

    /**
     * @return string
     */
    public function getSolution()
    {
        return __DIR__ . '/../../res/solutions/baby-steps/solution.php';
    }

    /**
     * @return array
     */
    public function getArgs()
    {
        $numArgs = rand(0, 10);

        $args = [];
        for ($i = 0; $i < $numArgs; $i ++) {
            $args[] = rand(0, 100);
        }

        return $args;
    }
}