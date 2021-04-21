<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshopTest;

use PhpSchool\PhpWorkshop\Utils\Path;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

abstract class BaseTest extends TestCase
{
    private $tempDirectory;

    public function getTemporaryDirectory(): string
    {
        if (!$this->tempDirectory) {
            $tempDirectory = sprintf('%s/%s', realpath(sys_get_temp_dir()), $this->getName());
            mkdir($tempDirectory, 0777, true);

            $this->tempDirectory = realpath($tempDirectory);
        }

        return $this->tempDirectory;
    }

    public function getTemporaryFile(string $filename): string
    {
        $file = Path::join($this->getTemporaryDirectory(), $filename);

        if (file_exists($file)) {
            return $file;
        }

        @mkdir(dirname($file), 0777, true);
        touch($file);

        return $file;
    }

    public function tearDown(): void
    {
        if ($this->tempDirectory) {
            (new Filesystem())->remove($this->tempDirectory);
        }
    }
}
