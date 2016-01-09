<?php

namespace PhpSchool\PhpWorkshop\ExerciseRunner;

use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\Output;
use PhpSchool\PhpWorkshop\Result\ResultInterface;

/**
 * Interface ExerciseRunnerInterface
 * @package PhpSchool\PhpWorkshop\ExerciseRunner
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
interface ExerciseRunnerInterface
{
    /**
     * @return string
     */
    public function getName();

    /**
     * @param ExerciseInterface $exercise
     * @param string $fileName
     * @return ResultInterface
     */
    public function verify(ExerciseInterface $exercise, $fileName);

    /**
     * @param ExerciseInterface $exercise
     * @param string $fileName
     * @param Output $output
     * @return bool
     */
    public function run(ExerciseInterface $exercise, $fileName, Output $output);
}
