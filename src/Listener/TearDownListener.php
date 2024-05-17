<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Listener;

use PhpSchool\PhpWorkshop\Utils\System;
use Symfony\Component\Filesystem\Filesystem;

class TearDownListener
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    public function cleanupTempDir(): void
    {
        $this->filesystem->remove(System::tempDir());
    }
}
