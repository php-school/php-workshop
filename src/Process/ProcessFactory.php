<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Process;

use PhpSchool\PhpWorkshop\ExerciseRunner\Context\Environment;
use PhpSchool\PhpWorkshop\Utils\ArrayObject;
use PhpSchool\PhpWorkshop\Utils\Collection;
use Symfony\Component\Process\Process;

interface ProcessFactory
{
    /**
     * @param array<string> $args
     * @param array<string, string> $env
     */
    public function create(
        string $executable,
        array $args,
        string $workingDirectory,
        array $env,
        string $input = null
    ): Process;
}
