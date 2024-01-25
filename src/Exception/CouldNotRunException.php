<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Exception;

use PhpSchool\PhpWorkshop\Result\FailureInterface;

class CouldNotRunException extends RuntimeException
{
    private FailureInterface $failure;

    public function __construct(FailureInterface $failure)
    {
        $this->failure = $failure;
        parent::__construct('Could not run exercise');
    }

    public static function fromFailure(FailureInterface $failure): self
    {
        return new self($failure);
    }

    public function getFailure(): FailureInterface
    {
        return $this->failure;
    }
}
