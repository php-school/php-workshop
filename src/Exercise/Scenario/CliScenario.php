<?php

namespace PhpSchool\PhpWorkshop\Exercise\Scenario;

use PhpSchool\PhpWorkshop\Utils\Collection;

class CliScenario extends ExerciseScenario
{
    /**
     * @var array<Collection<int, string>>
     */
    private array $executions = [];

    /**
     * @param array<string> $args
     */
    public function withExecution(array $args = []): static
    {
        $this->executions[] = new Collection($args);

        return $this;
    }

    /**
     * @return array<Collection<int, string>>
     */
    public function getExecutions(): array
    {
        return $this->executions;
    }
}
