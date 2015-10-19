<?php

namespace PhpSchool\PhpWorkshop\Exercise;

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
     * @return string
     */
    public function getDescription();

    /**
     * @return string
     */
    public function getSolution();

    /**
     * @return string
     */
    public function getProblem();

    /**
     * @return array
     */
    public function getArgs();

    /**
     * @return void
     */
    public function tearDown();
}
