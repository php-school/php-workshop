<?php

namespace PhpSchool\PhpWorkshopTest\Exercise;

use PhpSchool\PhpWorkshopTest\Asset\AbstractExerciseImpl;
use PHPUnit_Framework_TestCase;
use ReflectionClass;

/**
 * Class AbstractExerciseTest
 * @package PhpSchool\PhpWorkshopTest\Exercise
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class AbstractExerciseTest extends PHPUnit_Framework_TestCase
{
    public function testTearDownReturnsVoid()
    {
        $exercise = new AbstractExerciseImpl('name');
        $this->assertNull($exercise->tearDown());
    }

    /**
     * @dataProvider solutionProvider
     * @param string $name
     * @param string $path
     */
    public function testGetSolution($name, $path)
    {
        $exercise = new AbstractExerciseImpl($name);
        $this->assertSame($path, $exercise->getSolution());
    }

    /**
     * @return array
     */
    public function solutionProvider()
    {
        $reflector  = new ReflectionClass(AbstractExerciseImpl::class);
        $dir        = dirname($reflector->getFileName());
        return [
            ['Array We Go!', sprintf('%s/../../res/solutions/array-we-go/solution.php', $dir)],
            ['Array We Go', sprintf('%s/../../res/solutions/array-we-go/solution.php', $dir)],
            ['Array^7-We Go', sprintf('%s/../../res/solutions/array-we-go/solution.php', $dir)],
        ];
    }

    /**
     * @dataProvider problemProvider
     * @param string $name
     * @param string $path
     */
    public function testGetProblem($name, $path)
    {
        $exercise = new AbstractExerciseImpl($name);
        $this->assertSame($path, $exercise->getProblem());
    }

    /**
     * @return array
     */
    public function problemProvider()
    {
        $reflector  = new ReflectionClass(AbstractExerciseImpl::class);
        $dir        = dirname($reflector->getFileName());
        return [
            ['Array We Go!', sprintf('%s/../../res/problems/array-we-go/problem.md', $dir)],
            ['Array We Go', sprintf('%s/../../res/problems/array-we-go/problem.md', $dir)],
            ['Array^7-We Go', sprintf('%s/../../res/problems/array-we-go/problem.md', $dir)],
        ];
    }
}
