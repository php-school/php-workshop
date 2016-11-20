<?php

namespace PhpSchool\PhpWorkshop\ExerciseCheck;

use PhpSchool\PhpWorkshop\Input\Input;
use PhpSchool\PhpWorkshop\Result\ResultInterface;

/**
 * When implemented in an exercise, this interface allows for an exercise to check it's self.
 * That is, perform additional verifications in the actual exercise class
 * itself. See [Self Checking Exercises](https://www.phpschool.io/docs/reference/self-checking-exercises) for more
 * information.
 *
 * Self checking runs *after* the student's solution has been run/verified.
 *
 * @package PhpSchool\PhpWorkshop\ExerciseCheck
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
interface SelfCheck
{
    /**
     * The method is passed the absolute file path to the student's solution and should return a result
     * object which indicates the success or not of the check.
     *
     * @param Input $input The command line arguments passed to the command.
     * @return ResultInterface The result of the check.
     */
    public function check(Input $input);
}
