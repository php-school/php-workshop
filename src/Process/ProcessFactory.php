<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Process;

use PhpSchool\PhpWorkshop\Utils\ArrayObject;
use PhpSchool\PhpWorkshop\Utils\Collection;
use Symfony\Component\Process\Process;

interface ProcessFactory
{
    /**
     * @param array<string> $composerArgs
     */
    public function composer(string $solutionPath, string $composerCommand, array $composerArgs): Process;

    /**
     * @param Collection<int, string> $args
     */
    public function phpCli(string $fileName, Collection $args): Process;

    /**
     * @param array<string, string|false> $env
     */
    public function phpCgi(string $solutionPath, array $env, string $content): Process;
}
