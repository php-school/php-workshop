<?php

namespace PhpSchool\PhpWorkshop\Event;

use PhpSchool\PhpWorkshop\Exercise\Scenario\CliScenario;
use PhpSchool\PhpWorkshop\ExerciseRunner\Context\ExecutionContext;

class CliExerciseRunnerEvent extends ExerciseRunnerEvent
{
    private CliScenario $scenario;

    /**
     * @param array<string, mixed> $parameters
     */
    public function __construct(
        string $name,
        ExecutionContext $context,
        CliScenario $scenario,
        array $parameters = [],
    ) {
        $this->scenario = $scenario;
        parent::__construct($name, $context, $parameters);
    }

    public function getScenario(): CliScenario
    {
        return $this->scenario;
    }
}
