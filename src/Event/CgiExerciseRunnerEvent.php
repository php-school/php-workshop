<?php

namespace PhpSchool\PhpWorkshop\Event;

use PhpSchool\PhpWorkshop\Environment\CgiTestEnvironment;
use PhpSchool\PhpWorkshop\ExerciseRunner\Context\ExecutionContext;
use PhpSchool\PhpWorkshop\Environment\CliTestEnvironment;

class CgiExerciseRunnerEvent extends ExerciseRunnerEvent
{
    public CgiTestEnvironment $environment;

    /**
     * @param array<mixed> $parameters
     */
    public function __construct(
        string $name,
        ExecutionContext $context,
        CgiTestEnvironment $environment,
        array $parameters = []
    ) {
        $this->environment = $environment;
        parent::__construct($name, $context, $parameters);
    }
}
