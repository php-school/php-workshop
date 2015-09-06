<?php

namespace PhpWorkshop\PhpWorkshop\Exercise;

/**
 * Class ExerciseInterface
 * @package PhpWorkshop\PhpWorkshop\Exercise
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
}
