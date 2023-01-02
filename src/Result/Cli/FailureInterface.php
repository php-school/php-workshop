<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Result\Cli;

/**
 * This interface represents a failure. Any result implementing this interface will
 * be treated as a failure.
 */
interface FailureInterface extends ResultInterface
{
    /**
     * Return the failure data as an array
     *
     * @return array
     */
    public function toArray(): array;
}
