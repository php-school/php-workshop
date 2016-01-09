<?php

namespace PhpSchool\PhpWorkshop\Check;

use PhpSchool\CliMenu\Exception\InvalidTerminalException;
use PhpSchool\PhpWorkshop\Exception\InvalidArgumentException;
use PhpSchool\PhpWorkshop\Exercise\ExerciseType;

/**
 * Class CheckCollection
 * @package PhpSchool\PhpWorkshop\Check
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class CheckCollection
{
    /**
     * @var CheckInterface[]
     */
    private $checks = [];

    /**
     * @param CheckInterface[] $checks
     */
    public function __construct(array $checks)
    {
        foreach ($checks as $check) {
            $this->registerCheck($check);
        }
    }

    /**
     * @param CheckInterface $check
     */
    private function registerCheck(CheckInterface $check)
    {
        $this->checks[get_class($check)] = $check;
    }

    /**
     * @return array
     */
    public function getAll()
    {
        return $this->checks;
    }

    /**
     * @param ExerciseType $type
     * @return array
     */
    public function getAllByExerciseType(ExerciseType $type)
    {
        return array_filter($this->checks, function (CheckInterface $check) use ($type) {
            return $check->appliesTo() == $type;
        });
    }

    /**
     * @param string $class
     * @return CheckInterface
     * @throws InvalidArgumentException
     */
    public function getByClass($class)
    {
        if (!isset($this->checks[$class])) {
            throw new InvalidArgumentException(sprintf('Check: "%s" does not exist', $class));
        }

        return $this->checks[$class];
    }

    /**
     * @param string $class
     * @return bool
     */
    public function has($class)
    {
        try {
            $this->getByClass($class);
            return true;
        } catch (InvalidArgumentException $e) {
            return false;
        }
    }
}
