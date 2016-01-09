<?php

namespace PhpSchool\PhpWorkshop\Exercise;

use PhpSchool\PhpWorkshop\ExerciseDispatcher;
use PhpSchool\PhpWorkshop\Solution\SolutionInterface;

/**
 * Class ExerciseInterface
 * @package PhpSchool\PhpWorkshop\Exercise
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */

interface ExerciseInterface
{

    /**
     * @return string
     */
    public function getName();

    /**
     * @return ExerciseType
     */
    public function getType();

    /**
     * @param ExerciseDispatcher $dispatcher
     */
    public function configure(ExerciseDispatcher $dispatcher);

    /**
     * @return string
     */
    public function getDescription();

    /**
     * @return SolutionInterface
     */
    public function getSolution();

    /**
     * @return string
     */
    public function getProblem();

    /**
     * @return void
     */
    public function tearDown();
}
