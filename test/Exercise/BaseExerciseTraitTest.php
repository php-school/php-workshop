<?php

namespace PhpSchool\PhpWorkshopTest\Exercise;

use PhpSchool\PhpWorkshop\Exercise\ExerciseAssets;
use PhpSchool\PhpWorkshop\Solution\SolutionFile;
use PhpSchool\PhpWorkshopTest\Asset\AbstractExerciseImpl;
use PhpSchool\PhpWorkshopTest\Asset\BaseExerciseImpl;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

class BaseExerciseTraitTest extends TestCase
{
    private $tmpExerciseAssetDir;

    public function setUp(): void
    {
        $this->tmpExerciseAssetDir = sprintf('%s/%s', realpath(sys_get_temp_dir()), $this->getName());
        mkdir($this->tmpExerciseAssetDir, 0777, true);

        ExerciseAssets::init($this->tmpExerciseAssetDir);
    }

    public function tearDown(): void
    {
        (new Filesystem())->remove($this->tmpExerciseAssetDir);
    }

    /**
     * @dataProvider solutionProvider
     */
    public function testGetSolution(string $name): void
    {
        $exercise = new BaseExerciseImpl($name);
        $path = "{$this->tmpExerciseAssetDir}/array-we-go/solution/solution.php";

        mkdir(dirname($path), 0777, true);
        touch($path);

        $solution = $exercise->getSolution();
        $files = $solution->getFiles();
        $this->assertCount(1, $files);
        $this->assertInstanceOf(SolutionFile::class, $files[0]);
        $this->assertSame($path, $files[0]->__toString());
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
        $exercise = new BaseExerciseImpl($name);
        $this->assertSame("$this->tmpExerciseAssetDir/$path", $exercise->getProblem());
    }

    public function problemProvider(): array
    {
        return [
            ['Array We Go!', 'array-we-go/problem/problem.md'],
            ['Array We Go', 'array-we-go/problem/problem.md'],
            ['Array^7-We Go', 'array-we-go/problem/problem.md'],
        ];
    }
}
