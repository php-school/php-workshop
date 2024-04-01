<?php

namespace PhpSchool\PhpWorkshop\ExerciseRunner\Context;

use PhpSchool\PhpWorkshop\Utils\Collection;

class CgiContext implements RunnerContext
{
    public function __construct(public ExecutionContext $context)
    {
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
