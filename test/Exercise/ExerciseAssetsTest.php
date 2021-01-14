<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshopTest\Exercise;

use PhpSchool\PhpWorkshop\Exception\AssetsNotInitialisedException;
use PhpSchool\PhpWorkshop\Exception\InvalidArgumentException;
use PhpSchool\PhpWorkshop\Exercise\ExerciseAssets;
use PhpSchool\PhpWorkshopTest\Asset\BaseExerciseImpl;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

class ExerciseAssetsTest extends TestCase
{
    /**
     * @runInSeparateProcess
     */
    public function testExceptionIsThrownIfUsedBeforeInitialised(): void
    {
        $this->expectException(AssetsNotInitialisedException::class);
        $this->expectExceptionMessage('Assets not initialised with a base path');

        $exercise = new BaseExerciseImpl('my-exercise');

        ExerciseAssets::getAssetPath($exercise, 'solution', 'solution.php');
    }

    /**
     * @runInSeparateProcess
     */
    public function testGetAssetPathThrowsExceptionForInvalidType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Value "not-a-type" is not an element of the valid values: solution, problem, initial'
        );

        ExerciseAssets::init(sys_get_temp_dir());
        $exercise = new BaseExerciseImpl('my-exercise');

        ExerciseAssets::getAssetPath($exercise, 'not-a-type', 'solution.php');
    }

    /**
     * @runInSeparateProcess
     */
    public function testGetAssets(): void
    {
        $path = sprintf('%s/%s', realpath(sys_get_temp_dir()), $this->getName());
        mkdir($path, 0777, true);

        ExerciseAssets::init($path);
        $exercise = new BaseExerciseImpl('my-exercise');

        $this->assertSame(
            "$path/my-exercise/solution/solution.php",
            ExerciseAssets::getAssetPath($exercise, 'solution', 'solution.php')
        );
        $this->assertSame(
            "$path/my-exercise/problem/problem.md",
            ExerciseAssets::getAssetPath($exercise, 'problem', 'problem.md')
        );
        $this->assertSame(
            "$path/my-exercise/initial/solution.php",
            ExerciseAssets::getAssetPath($exercise, 'initial', 'solution.php')
        );

        (new Filesystem())->remove($path);
    }
}
