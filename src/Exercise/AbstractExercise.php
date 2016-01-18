<?php

namespace PhpSchool\PhpWorkshop\Exercise;

use PhpSchool\PhpWorkshop\ExerciseDispatcher;
use PhpSchool\PhpWorkshop\Solution\SingleFileSolution;
use PhpSchool\PhpWorkshop\Solution\SolutionInterface;
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
     * @return SolutionInterface
     */
    public function getSolution()
    {
        return SingleFileSolution::fromFile(
            realpath(
                sprintf(
                    '%s/../../exercises/%s/solution/solution.php',
                    dirname((new ReflectionClass(static::class))->getFileName()),
                    $this->normaliseName($this->getName())
                )
            )
        );
    }

    /**
     * @return string
     */
    public function getProblem()
    {
        $name = $this->normaliseName($this->getName());
        $dir  = dirname((new ReflectionClass(static::class))->getFileName());
        return sprintf('%s/../../exercises/%s/problem/problem.md', $dir, $name);
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

    /**
     * @param ExerciseDispatcher $dispatcher
     */
    public function configure(ExerciseDispatcher $dispatcher)
    {
    }
}
