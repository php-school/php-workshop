<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Check;

use PhpSchool\PhpWorkshop\Exception\InvalidArgumentException;

/**
 * This class is the repository containing all the available checks
 * for the workshop framework.
 */
class CheckRepository
{
    /**
     * @var array<CheckInterface>
     */
    private $checks = [];

    /**
     * @param array<CheckInterface> $checks An array of checks available to the workshop framework.
     */
    public function __construct(array $checks = [])
    {
        foreach ($checks as $check) {
            $this->registerCheck($check);
        }
    }

    /**
     * Add a new check to the repository.
     * @param CheckInterface $check
     */
    public function registerCheck(CheckInterface $check): void
    {
        $this->checks[get_class($check)] = $check;
    }

    /**
     * Get all of the checks in the repository.
     *
     * @return array<CheckInterface>
     */
    public function getAll(): array
    {
        return array_values($this->checks);
    }

    /**
     * Get a check instance via it's class name.
     *
     * @param string $class The class name of the check instance.
     * @return CheckInterface The instance.
     * @throws InvalidArgumentException If an instance of the check does not exist.
     */
    public function getByClass(string $class): CheckInterface
    {
        if (!isset($this->checks[$class])) {
            throw new InvalidArgumentException(sprintf('Check: "%s" does not exist', $class));
        }

        return $this->checks[$class];
    }

    /**
     * Query whether a check instance exists in this repository via its class name.
     *
     * @param string $class
     * @return bool
     */
    public function has(string $class): bool
    {
        try {
            $this->getByClass($class);
            return true;
        } catch (InvalidArgumentException $e) {
            return false;
        }
    }
}
