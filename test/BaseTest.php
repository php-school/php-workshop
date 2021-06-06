<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshopTest;

use PhpSchool\PhpWorkshop\Utils\Path;
use PhpSchool\PhpWorkshop\Utils\System;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

abstract class BaseTest extends TestCase
{
    private $tempDirectory;

    public function getTemporaryDirectory(): string
    {
        if (!$this->tempDirectory) {
            $this->tempDirectory = System::tempDir($this->getName());
            mkdir($this->tempDirectory, 0777, true);
        }

        return $this->tempDirectory;
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

    public function tearDown(): void
    {
        if ($this->tempDirectory) {
            (new Filesystem())->remove($this->tempDirectory);
        }
    }
}
