<?php

namespace PhpSchool\PhpWorkshop\Environment;

use PhpSchool\PhpWorkshop\Utils\Collection;

class CliTestEnvironment extends TestEnvironment
{
    /**
     * @var array<Collection<int, string>>
     */
    public array $executions = [];

    /**
     * @param array<string> $args
     */
    public function withExecution(array $args = []): static
    {
        $this->executions[] = new Collection($args);

        return $this;
    }
}
