<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Process;

use Symfony\Component\Process\Process;

interface ProcessFactory
{
    public function composer(string $solitionPath, string $composerCommand, array $composerArgs): Process;

    public function phpCli(string $fileName, array $args): Process;

    public function phpCgi(string $solutionPath, array $env, string $content): Process;
}
