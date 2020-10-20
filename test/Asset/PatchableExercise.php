<?php

namespace PhpSchool\PhpWorkshopTest\Asset;

use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\Exercise\ExerciseType;
use PhpSchool\PhpWorkshop\Exercise\SubmissionPatchable;
use PhpSchool\PhpWorkshop\ExerciseDispatcher;
use PhpSchool\PhpWorkshop\Patch;

/**
 * Class PatchableExercise
 * @package PhpSchool\PhpWorkshopTest\Asset
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class PatchableExercise implements ExerciseInterface, SubmissionPatchable
{
    public function getName() : string
    {
        // TODO: Implement getName() method.
    }

    public function getDescription() : string
    {
        // TODO: Implement getDescription() method.
    }

    public function getSolution() : string
    {
        // TODO: Implement getSolution() method.
    }

    public function getProblem() : string
    {
        // TODO: Implement getProblem() method.
    }

    public function tearDown() : void
    {
        // TODO: Implement tearDown() method.
    }

    public function getPatch() : Patch
    {
        // TODO: Implement getPatch() method.
    }

    public function getType() : ExerciseType
    {
        // TODO: Implement getType() method.
    }

    public function configure(ExerciseDispatcher $dispatcher) : void
    {
        // TODO: Implement configure() method.
    }
}
