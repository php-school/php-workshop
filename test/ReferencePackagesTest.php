<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshopTest;

use PhpSchool\PhpWorkshop\ExerciseRepository;
use PhpSchool\PhpWorkshop\Process\HostSandbox;
use PhpSchool\PhpWorkshop\ReferencePackages;
use PhpSchool\PhpWorkshop\Solution\DirectorySolution;
use PhpSchool\PhpWorkshop\Utils\System;
use PhpSchool\PhpWorkshopTest\Asset\CliExerciseImpl;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

class ReferencePackagesTest extends TestCase
{
    public function testWarmLocksEachSolutionWithPackages(): void
    {
        $directory = System::randomTempDir();
        mkdir($directory, 0777, true);
        file_put_contents($directory . '/solution.php', '<?php');
        file_put_contents($directory . '/composer.json', json_encode(['require' => ['psr/log' => '^3.0']]));

        $withPackages = new CliExerciseImpl();
        $withPackages->setSolution(DirectorySolution::fromDirectory($directory));

        (new ReferencePackages(new ExerciseRepository([$withPackages]), new HostSandbox()))->warm();

        $this->assertFileExists($directory . '/composer.lock');
        $this->assertDirectoryDoesNotExist($directory . '/vendor');
        $this->assertContains('composer.lock', array_map(
            fn($file) => $file->getRelativePath(),
            DirectorySolution::fromDirectory($directory)->getFiles(),
        ));

        (new Filesystem())->remove($directory);
    }
}
