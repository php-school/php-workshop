<?php

namespace PhpSchool\PhpWorkshopTest\Asset;

use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\Exercise\ExerciseType;
use PhpSchool\PhpWorkshop\Exercise\PreProcessable;
use PhpSchool\PhpWorkshop\Exercise\SubmissionPatchable;
use PhpSchool\PhpWorkshop\ExerciseDispatcher;
use PhpSchool\PhpWorkshop\SubmissionPatch;

/**
 * Class PatchableExercise
 * @package PhpSchool\PhpWorkshopTest\Asset
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class PatchableExercise implements ExerciseInterface, SubmissionPatchable
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
        
    }

    /**
     * @return SubmissionPatch
     */
    public function getPatch()
    {
        // TODO: Implement getPatch() method.
    }

    /**
     * @return ExerciseType
     */
    public function getType()
    {
        // TODO: Implement getType() method.
    }

    /**
     * @param ExerciseDispatcher $dispatcher
     */
    public function configure(ExerciseDispatcher $dispatcher)
    {
        // TODO: Implement configure() method.
    }
}
