<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Process\Sandbox\Host;

use PhpSchool\PhpWorkshop\Process\Sandbox\SandboxFactory;
use PhpSchool\PhpWorkshop\Exercise\Scenario\ExerciseScenario;
use PhpSchool\PhpWorkshop\Process\Port\FreePortPicker;
use Symfony\Component\Process\ExecutableFinder;

final class HostSandboxFactory implements SandboxFactory
{
    public function __construct(private ExecutableFinder $executableFinder = new ExecutableFinder()) {}

    public function create(string $directory, ExerciseScenario $scenario): HostSandbox
    {
        $port = $scenario->exposesPort() ? (new FreePortPicker())->pick() : null;

        return new HostSandbox($directory, $port, $this->executableFinder);
    }
}
