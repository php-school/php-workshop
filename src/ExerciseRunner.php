<?php

namespace PhpSchool\PhpWorkshop;

use PhpSchool\PhpWorkshop\Check\CheckInterface;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\ExerciseCheck\SelfCheck;
use PhpSchool\PhpWorkshop\Result\Failure;

/**
 * Class ExerciseRunner
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */

class ExerciseRunner
{
    /**
     * @var CheckInterface[]
     */
    private $checks = [];

    /**
     * @var array
     */
    private $checkMap = [];

    /**
     * @param CheckInterface $check
     * @param string $exerciseInterface
     */
    public function registerCheck(CheckInterface $check, $exerciseInterface = null)
    {
        if (null !== $exerciseInterface && !is_string($exerciseInterface)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expected a string. Got: "%s"',
                    is_object($exerciseInterface) ? get_class($exerciseInterface) : gettype($exerciseInterface)
                )
            );
        }

        $lookUp                     = spl_object_hash($check);
        $this->checks[$lookUp]      = $check;
        $this->checkMap[$lookUp]    = $exerciseInterface;
    }

    /**
     * @param ExerciseInterface $exercise
     * @param string $fileName
     * @return ResultAggregator
     */
    public function runExercise(ExerciseInterface $exercise, $fileName)
    {
        $resultAggregator = new ResultAggregator;

        foreach ($this->checks as $check) {
            $exerciseInterface = $this->checkMap[spl_object_hash($check)];

            if (!is_subclass_of($exercise, $exerciseInterface)) {
                continue;
            }

            $result = $check->check($exercise, $fileName);
            $resultAggregator->add($result);

            if ($result instanceof Failure && $check->breakChainOnFailure()) {
                return $resultAggregator;
            }
        }

        if ($exercise instanceof SelfCheck) {
            $resultAggregator->add($exercise->check($fileName));
        }

        $exercise->tearDown();

        return $resultAggregator;
    }
}
