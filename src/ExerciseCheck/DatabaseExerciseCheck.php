<?php

namespace PhpSchool\PhpWorkshop\ExerciseCheck;

use PDO;

/**
 * Interface DatabaseExerciseCheck
 * @package PhpSchool\PhpWorkshop\ExerciseCheck
 */
interface DatabaseExerciseCheck
{
    /**
     * @param PDO $db
     * @return bool
     */
    public function verify(PDO $db);

    /**
     * @param PDO $db
     * @return void
     */
    public function seed(PDO $db);
}
