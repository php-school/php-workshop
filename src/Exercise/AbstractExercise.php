<?php

namespace PhpSchool\PhpWorkshop\Exercise;

use ReflectionClass;

/**
 * Class AbstractExercise
 * @package PhpSchool\PhpWorkshop\Exercise
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
abstract class AbstractExercise
{

    /**
     * @return string
     */
    abstract public function getName();

    /**
     * @return string
     */
    public function getSolution()
    {
        $name = $this->normaliseName($this->getName());
        $dir  = dirname((new ReflectionClass(static::class))->getFileName());
        return sprintf('%s/../../res/solutions/%s/solution.php', $dir, $name);
    }

    /**
     * @return string
     */
    public function getProblem()
    {
        $name = $this->normaliseName($this->getName());
        $dir  = dirname((new ReflectionClass(static::class))->getFileName());
        return sprintf('%s/../../res/problems/%s/problem.md', $dir, $name);
    }

    /**
     * @return null
     */
    public function tearDown()
    {
    }

    /**
     * @param string $name
     * @return string
     */
    private function normaliseName($name)
    {
        return preg_replace('/[^A-Za-z\-]+/', '', str_replace(' ', '-', strtolower($name)));
    }
}
