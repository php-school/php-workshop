<?php

namespace PhpSchool\PhpWorkshop\Exercise;

use PhpSchool\PhpWorkshop\ExerciseDispatcher;
use PhpSchool\PhpWorkshop\Solution\SolutionInterface;

/**
 * This interface describes all of the methods an exercise
 * should implement.
 *
 * @package PhpSchool\PhpWorkshop\Exercise
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
interface ExerciseInterface
{

    /**
     * Get the name of the exercise, like `Hello World!`.
     *
     * @return string
     */
    public function getName();

    /**
     * Return the type of exercise. This is an ENUM. See `PhpSchool\PhpWorkshop\Exercise\ExerciseType`.
     *
     * @return ExerciseType
     */
    public function getType();

    /**
     * This is where the exercise specifies the extra checks it may require. It is also
     * possible to grab the event dispatcher from the exercise dispatcher and listen to any
     * events. This method is automatically invoked just before verifying/running an student's solution
     * to an exercise.
     *
     * @param ExerciseDispatcher $dispatcher
     */
    public function configure(ExerciseDispatcher $dispatcher);

    /**
     * A short description of the exercise.
     *
     * @return string
     */
    public function getDescription();

    /**
     * Allows to perform some cleanup after the exercise solution's have been executed, for example
     * remove files, close DB connections.
     *
     * @return void
     */
    public function tearDown();
}
