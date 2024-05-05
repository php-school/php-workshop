<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshopTest;

use PhpSchool\PhpWorkshop\ExerciseRunner\Context\Environment;
use PhpSchool\PhpWorkshop\Utils\Path;
use PhpSchool\PhpWorkshop\Utils\System;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

abstract class BaseTest extends TestCase
{
    private $tempDirectory;

    public function getTemporaryDirectory($create = true): string
    {
        if (!$this->tempDirectory) {
            $this->tempDirectory = System::tempDir($this->getName());
            if ($create) {
                mkdir($this->tempDirectory, 0777, true);
            }
        }

        return $this->tempDirectory;
    }

    public function createFileInEnvironment(string $workingDirectory, string $filename, string $content = null): string
    {
        $file = Path::join($workingDirectory, $filename);

        if (file_exists($file)) {
            return $file;
        }

        if (!file_exists(dirname($file))) {
            mkdir(dirname($file), 0777, true);
        }

        $content !== null
            ? file_put_contents($file, $content)
            : touch($file);

        return $file;
    }

    public function getTemporaryFile(string $filename, string $content = null): string
    {
        $file = Path::join($this->getTemporaryDirectory(), $filename);

        if (file_exists($file)) {
            return $file;
        }

        if (!file_exists(dirname($file))) {
            mkdir(dirname($file), 0777, true);
        }

        $content !== null
            ? file_put_contents($file, $content)
            : touch($file);

        return $file;
    }

    protected function tearDown(): void
    {
        if (file_exists(System::tempDir($this->getName()))) {
            (new Filesystem())->remove(System::tempDir($this->getName()));
        }
    }
}
