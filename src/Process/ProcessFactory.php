<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Process;

use Symfony\Component\Process\Process;

interface ProcessFactory
{
    public function create(ProcessInput $processInput): Process;
}
