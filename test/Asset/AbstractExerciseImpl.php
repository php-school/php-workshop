<?php

namespace PhpSchool\PhpWorkshopTest\Asset;

use PhpSchool\PhpWorkshop\Exercise\AbstractExercise;

/**
 * Class AbstractExerciseImpl
 * @package PhpSchool\PhpWorkshopTest\Asset
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class AbstractExerciseImpl extends AbstractExercise
{

    /**
     * @var string
     */
    private $name;

    /**
     * @param string $name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}
