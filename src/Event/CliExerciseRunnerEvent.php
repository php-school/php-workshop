<?php

namespace PhpSchool\PhpWorkshop\Event;

use PhpSchool\PhpWorkshop\ExerciseRunner\Context\ExecutionContext;
use PhpSchool\PhpWorkshop\Environment\CliTestEnvironment;

class CliExerciseRunnerEvent extends ExerciseRunnerEvent
{
    public CliTestEnvironment $environment;

    /**
     * @param array<mixed> $parameters
     */
    public function __construct(
        string $name,
        ExecutionContext $context,
        CliTestEnvironment $environment,
        array $parameters = []
    ) {
        $this->environment = $environment;
        parent::__construct($name, $context, $parameters);
    }
}
