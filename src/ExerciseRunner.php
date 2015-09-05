<?php

namespace PhpWorkshop\PhpWorkshop;

use PhpWorkshop\PhpWorkshop\Check\FileExistsCheck;
use PhpWorkshop\PhpWorkshop\Check\PhpLintCheck;
use PhpWorkshop\PhpWorkshop\Exercise\ExerciseInterface;
use PhpWorkshop\PhpWorkshop\Check\StdOutCheck;
use PhpWorkshop\PhpWorkshop\ExerciseCheck\StdOutExerciseCheck;

/**
 * Class ExerciseRunner
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */

class ExerciseRunner
{
    /**
     * @var FileExistsCheck
     */
    private $fileExistsCheck;

    /**
     * @var PhpLintCheck
     */
    private $lintCheck;

    /**
     * @var StdOutCheck
     */
    private $stdOutCheck;

    /**
     * @param PhpLintCheck $lintCheck
     * @param StdOutCheck $stdOutCheck
     * @param FileExistsCheck $fileExistsCheck
     */
    public function __construct(
        FileExistsCheck $fileExistsCheck,
        PhpLintCheck $lintCheck,
        StdOutCheck $stdOutCheck
    ) {
        $this->fileExistsCheck  = $fileExistsCheck;
        $this->lintCheck        = $lintCheck;
        $this->stdOutCheck      = $stdOutCheck;
    }

    /**
     * @param ExerciseInterface $exercise
     * @param $fileName
     * @return ResultAggregator
     */
    public function runExercise(ExerciseInterface $exercise, $fileName)
    {

        $resultAggregator = new ResultAggregator;

        $result = $this->fileExistsCheck->check($exercise, $fileName);
        $resultAggregator->add($result);

        //return early
        if ($result instanceof Fail) {
            return $resultAggregator;
        }

        $result = $this->lintCheck->check($exercise, $fileName);
        $resultAggregator->add($result);

        //return early
        if ($result instanceof Fail) {
            return $resultAggregator;
        }

        if ($exercise instanceof StdOutExerciseCheck) {
            $resultAggregator->add(
                $this->stdOutCheck->check($exercise, $fileName)
            );
        }

        return $resultAggregator;
    }
}