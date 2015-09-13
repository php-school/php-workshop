<?php

namespace PhpWorkshop\PhpWorkshop\Exercise;

use Faker\Generator;
use PhpWorkshop\PhpWorkshop\ExerciseCheck\FunctionRequirementsExerciseCheck;
use PhpWorkshop\PhpWorkshop\ExerciseCheck\StdOutExerciseCheck;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class MyFirstIo
 * @package PhpWorkshop\PhpWorkshop\Exercise
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class MyFirstIo implements
    ExerciseInterface,
    StdOutExerciseCheck,
    FunctionRequirementsExerciseCheck
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var Generator
     */
    private $faker;

    /**
     * @param Filesystem $filesystem
     * @param Generator $faker
     */
    public function __construct(Filesystem $filesystem, Generator $faker)
    {
        $this->filesystem   = $filesystem;
        $this->faker        = $faker;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'My First IO';
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return 'Read a file from the file system';
    }

    /**
     * @return string
     */
    public function getSolution()
    {
        return __DIR__ . '/../../res/solutions/my-first-io/solution.php';
    }

    /**
     * @return string
     */
    public function getProblem()
    {
        return __DIR__ . '/../../res/problems/my-first-io/problem.md';
    }

    /**
     * @return array
     */
    public function getArgs()
    {
        $path = sprintf('%s/%s', sys_get_temp_dir(), str_replace('\\', '_', __CLASS__));

        $paragraphs = $this->faker->paragraphs(rand(5, 50), true);
        $this->filesystem->dumpFile($path, $paragraphs);

        return [$path];
    }

    /**
     * @return null
     */
    public function tearDown()
    {
        $path = sprintf('%/%s', sys_get_temp_dir(), str_replace('\\', '_', __CLASS__));
        $this->filesystem->remove($path);
    }

    /**
     * @return string[]
     */
    public function getRequiredFunctions()
    {
        return ['file_get_contents'];
    }

    /**
     * @return string[]
     */
    public function getBannedFunctions()
    {
        return ['file'];
    }
}
