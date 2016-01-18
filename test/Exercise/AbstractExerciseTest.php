<?php

namespace PhpSchool\PhpWorkshopTest\Exercise;

use PhpSchool\PhpWorkshop\ExerciseDispatcher;
use PhpSchool\PhpWorkshop\Solution\SolutionFile;
use PhpSchool\PhpWorkshop\Solution\SolutionInterface;
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
     */
    public function testGetSolution($name)
    {
        $exercise   = new AbstractExerciseImpl($name);
        $path       = __DIR__ . '/../../exercises/array-we-go/solution/solution.php';
        mkdir(dirname($path), 0777, true);
        touch($path);
        $solution = $exercise->getSolution();
        $this->assertInstanceOf(SolutionInterface::class, $solution);
        $files = $solution->getFiles();
        $this->assertCount(1, $files);
        $this->assertInstanceOf(SolutionFile::class, $files[0]);
        $this->assertSame(realpath($path), $files[0]->__toString());
        unlink($path);
        rmdir(__DIR__ . '/../../exercises/array-we-go/solution');
        rmdir(__DIR__ . '/../../exercises/array-we-go');
        rmdir(__DIR__ . '/../../exercises');
    }

    /**
     * @return array
     */
    public function solutionProvider()
    {
        return [
            ['Array We Go!'],
            ['Array We Go'],
            ['Array^7-We Go'],
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
            ['Array We Go!', sprintf('%s/../../exercises/array-we-go/problem/problem.md', $dir)],
            ['Array We Go', sprintf('%s/../../exercises/array-we-go/problem/problem.md', $dir)],
            ['Array^7-We Go', sprintf('%s/../../exercises/array-we-go/problem/problem.md', $dir)],
        ];
    }

    public function testConfigureDoesNothing()
    {
        $dispatcher = $this->getMockBuilder(ExerciseDispatcher::class)
            ->disableOriginalConstructor()
            ->getMock();

        $exercise = new AbstractExerciseImpl('Array We Go');
        $this->assertNull($exercise->configure($dispatcher));
    }
}
