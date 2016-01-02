<?php

namespace PhpSchool\PhpWorkshopTest\Asset;

use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\ExerciseCheck\ComposerExerciseCheck;

/**
 * Class ComposerExercise
 * @package PhpSchool\PhpWorkshopTest\Asset
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class ComposerExercise implements ExerciseInterface, ComposerExerciseCheck
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
     * @return array[]
     */
    public function getRequiredPackages()
    {
        return [
            'klein/klein',
            'danielstjules/stringy'
        ];
    }
}
