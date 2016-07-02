<?php

namespace PhpSchool\PhpWorkshop\ExerciseCheck;

use PDO;

/**
 * This interface should be implemented when you require the check `PhpSchool\PhpWorkshop\Check\DatabaseCheck` in your
 * exercise.
 *
 * @package PhpSchool\PhpWorkshop\ExerciseCheck
 */
interface DatabaseExerciseCheck
{
    /**
     * This method allows your exercise to seed the database *before* the solution's are executed. You can do anything
     * you normally could with a `PDO` object.
     *
     * @param PDO $db A `PDO` instance pointing to the database which will be accessible to the student's solution.
     * @return void
     */
    public function seed(PDO $db);

    /**
     * This method allows your exercise to verify the state of database *after* the student's solution has been
     * executed. You can count rows in tables, check for the existence of tables & rows and of course anything else
     * you can do with a `PDO` object. The method should return a boolean indicating whether the verification
     * was successful or not.
     *
     * @param PDO $db A `PDO` instance pointing to the database which was accessible by the student's solution.
     * @return bool The result of the verification.
     */
    public function verify(PDO $db);
}
