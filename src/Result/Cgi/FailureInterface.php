<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Result\Cgi;

/**
 * This interface represents a failure. Any result implementing this interface will
 * be treated as a failure.
 */
interface FailureInterface extends ResultInterface
{
    /**
     * Return the failure data as an array
     *
     * @return array<string, mixed>
     */
    public function toArray(): array;
}
