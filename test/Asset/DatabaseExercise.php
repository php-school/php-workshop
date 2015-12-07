<?php

namespace PhpSchool\PhpWorkshopTest\Asset;

use PDO;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\ExerciseCheck\DatabaseExerciseCheck;

/**
 * Class DatabaseExercise
 * @package PhpSchool\PhpWorkshopTest
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class DatabaseExercise implements ExerciseInterface, DatabaseExerciseCheck
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
     * @return void
     */
    public function tearDown()
    {
        // TODO: Implement tearDown() method.
    }

    /**
     * @return array
     */
    public function getArgs()
    {
        // TODO: Implement getArgs() method.
    }

    /**
     * @param PDO $db
     * @return bool
     */
    public function verify(PDO $db)
    {
        // TODO: Implement verify() method.
    }

    /**
     * @param PDO $db
     * @return void
     */
    public function seed(PDO $db)
    {
        // TODO: Implement seed() method.
    }
}
