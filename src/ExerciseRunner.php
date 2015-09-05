<?php

namespace PhpWorkshop\PhpWorkshop;

use PhpWorkshop\PhpWorkshop\Comparator\StdOut;
use PhpWorkshop\PhpWorkshop\Exercise\ExerciseInterface;
use PhpWorkshop\PhpWorkshop\ExerciseCheck\StdOutCheck;

/**
 * Class ExerciseRunner
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */

class ExerciseRunner
{
    /**
     * @var StdOut
     */
    private $stdOutComparator;

    /**
     * @param StdOut $stdOutComparator
     */
    public function __construct(StdOut $stdOutComparator)
    {
        $this->stdOutComparator = $stdOutComparator;
    }

    /**
     * @param ExerciseInterface $exercise
     * @param $fileName
     * @return ResultAggregator
     */
    public function runExercise(ExerciseInterface $exercise, $fileName)
    {

        $resultAggregator = new ResultAggregator;

        if (!file_exists($fileName)) {
            $resultAggregator->add(new Fail($exercise, sprintf('File: "%s" does not exist', $fileName)));
        }

        //TODO: Lint?

        if ($exercise instanceof StdOutCheck) {
            $resultAggregator->add(
                $this->stdOutComparator->compare($exercise, $fileName)
            );
        }

        return $resultAggregator;
    }
}