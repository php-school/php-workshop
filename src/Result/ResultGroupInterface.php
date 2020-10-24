<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Result;

/**
 * A result to wrap multiple results
 */
interface ResultGroupInterface extends ResultInterface
{
    /**
     * @return bool
     */
    public function isSuccessful(): bool;

    /**
     * @return array<ResultInterface>
     */
    public function getResults(): array;
}
