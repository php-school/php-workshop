<?php

namespace PhpSchool\PhpWorkshop\Event;

use PhpSchool\PhpWorkshop\Exercise\Scenario\CgiScenario;
use PhpSchool\PhpWorkshop\ExerciseRunner\Context\ExecutionContext;

class CgiExerciseRunnerEvent extends ExerciseRunnerEvent
{
    private CgiScenario $scenario;

    /**
     * @param array<string, mixed> $parameters
     */
    public function __construct(
        string $name,
        ExecutionContext $context,
        CgiScenario $scenario,
        array $parameters = [],
    ) {
        $this->scenario = $scenario;
        parent::__construct($name, $context, $parameters);
    }

    public function getScenario(): CgiScenario
    {
        return $this->scenario;
    }
}
