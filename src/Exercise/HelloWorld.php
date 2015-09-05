<?php

namespace PhpWorkshop\PhpWorkshop\Exercise;

use PhpWorkshop\PhpWorkshop\ExerciseCheck\StdOutCheck;

/**
 * Class HelloWorld
 * @package PhpWorkshop\PhpWorkshop\Exercise
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class HelloWorld implements ExerciseInterface, StdOutCheck
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
        return 'NASA Level Programming';
    }

    /**
     * @return string
     */
    public function getSolution()
    {
        return __DIR__ . '/../../res/solutions/hello-world/solution.php';
    }

    /**
     * @return array
     */
    public function getArgs()
    {
        return [];
    }
}