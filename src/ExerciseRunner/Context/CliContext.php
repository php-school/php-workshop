<?php

namespace PhpSchool\PhpWorkshop\ExerciseRunner\Context;

use PhpSchool\PhpWorkshop\Utils\Collection;

class CliContext implements RunnerContext
{
    public function __construct(public ExecutionContext $context)
    {
    }

    /**
     * @var array<Collection<int, string>>
     */
    public array $runs = [];

    /**
     * @param array<string> $args
     */
    public function addRun(array $args): void
    {
        $this->runs[] = new Collection($args);
    }

    public function addFile(string $relativeFileName, string $content): void
    {
        $this->context->addFile($relativeFileName, $content);
    }

    public function getExecutionContext(): ExecutionContext
    {
        return $this->context;
    }
}
