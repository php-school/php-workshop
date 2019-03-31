<?php

namespace PhpSchool\PhpWorkshopTest\Asset;

use PhpSchool\PhpWorkshop\Check\ComposerCheck;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\Exercise\ExerciseType;
use PhpSchool\PhpWorkshop\ExerciseCheck\ComposerExerciseCheck;
use PhpSchool\PhpWorkshop\ExerciseDispatcher;

/**
 * Class ComposerExercise
 * @package PhpSchool\PhpWorkshopTest\Asset
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class ComposerExercise implements ExerciseInterface, ComposerExerciseCheck
{
    public function getName() : string
    {
        return 'composer-exercise';
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

    public function getArgs() : array
    {
        return []; // TODO: Implement getArgs() method.
    }

    public function getRequiredPackages() : array
    {
        return [
            'klein/klein',
            'danielstjules/stringy'
        ];
    }

    public function getType() : ExerciseType
    {
        return ExerciseType::CLI();
    }

    public function configure(ExerciseDispatcher $dispatcher) : void
    {
        $dispatcher->requireCheck(ComposerCheck::class);
    }
}
