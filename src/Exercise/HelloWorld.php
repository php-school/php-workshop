<?php

namespace PhpWorkshop\PhpWorkshop\Exercise;

use PhpWorkshop\PhpWorkshop\ExerciseCheck\StdOutExerciseCheck;

/**
 * Class HelloWorld
 * @package PhpWorkshop\PhpWorkshop\Exercise
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class HelloWorld implements ExerciseInterface, StdOutExerciseCheck
{

    /**
     * @return string
     */
    public function getName()
    {
        return 'Hello World';
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return 'Simple Hello World exercise';
    }

    /**
     * @return string
     */
    public function getSolution()
    {
        return __DIR__ . '/../../res/solutions/hello-world/solution.php';
    }

    /**
     * @return string
     */
    public function getProblem()
    {
        return __DIR__ . '/../../res/problems/hello-world/problem.md';
    }

    /**
     * @return array
     */
    public function getArgs()
    {
        return [];
    }
}
