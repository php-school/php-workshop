<?php

namespace PhpSchool\PhpWorkshop\ExerciseRunner;

use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\Output\OutputInterface;
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
     * @param string $fileName
     * @return ResultInterface
     */
    public function verify($fileName);

    /**
     * @param string $fileName
     * @param OutputInterface $output
     * @return bool
     */
    public function run($fileName, OutputInterface $output);
}
