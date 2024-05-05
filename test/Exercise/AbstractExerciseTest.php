<?php

namespace PhpSchool\PhpWorkshopTest\Exercise;

use PhpSchool\PhpWorkshop\Event\EventDispatcher;
use PhpSchool\PhpWorkshop\ExerciseDispatcher;
use PhpSchool\PhpWorkshop\Solution\SolutionFile;
use PhpSchool\PhpWorkshop\Solution\SolutionInterface;
use PhpSchool\PhpWorkshopTest\Asset\AbstractExerciseImpl;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class AbstractExerciseTest extends TestCase
{
    public function testTearDownReturnsVoid(): void
    {
        $exercise = new AbstractExerciseImpl('name');
        $this->assertNull($exercise->tearDown());
    }

    /**
     * @dataProvider solutionProvider
     */
    public function testGetSolution(string $name): void
    {
        $exercise   = new AbstractExerciseImpl($name);
        $path       = __DIR__ . '/../../exercises/array-we-go/solution/solution.php';
        mkdir(dirname($path), 0777, true);
        touch($path);
        $files = $exercise->getSolution()->getFiles();
        self::assertCount(1, $files);
        self::assertInstanceOf(SolutionFile::class, $files[0]);
        self::assertFileEquals(realpath($path), $files[0]->__toString());
        unlink($path);
        rmdir(__DIR__ . '/../../exercises/array-we-go/solution');
        rmdir(__DIR__ . '/../../exercises/array-we-go');
        rmdir(__DIR__ . '/../../exercises');
    }

    public function solutionProvider(): array
    {
        return [
            ['Array We Go!'],
            ['Array We Go'],
            ['Array^7-We Go'],
        ];
    }

    /**
     * @dataProvider problemProvider
     */
    public function testGetProblem(string $name, string $path): void
    {
        $exercise = new AbstractExerciseImpl($name);
        $this->assertSame($path, $exercise->getProblem());
    }

    public function problemProvider(): array
    {
        $reflector  = new ReflectionClass(AbstractExerciseImpl::class);
        $dir        = dirname($reflector->getFileName());
        return [
            ['Array We Go!', sprintf('%s/../../exercises/array-we-go/problem/problem.md', $dir)],
            ['Array We Go', sprintf('%s/../../exercises/array-we-go/problem/problem.md', $dir)],
            ['Array^7-We Go', sprintf('%s/../../exercises/array-we-go/problem/problem.md', $dir)],
        ];
    }

    public function testDefineListenersDoesNothing(): void
    {
        $dispatcher = $this->createMock(EventDispatcher::class);

        $exercise = new AbstractExerciseImpl('Array We Go');
        $this->assertNull($exercise->defineListeners($dispatcher));
    }
}
