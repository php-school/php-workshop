<?php

namespace PhpWorkshop\PhpWorkshop\Check;

use PhpWorkshop\PhpWorkshop\Exercise\ExerciseInterface;
use PhpWorkshop\PhpWorkshop\Success;
use PhpWorkshop\PhpWorkshop\Fail;
use Symfony\Component\Process\Process;

/**
 * Class FileExistsCheck
 * @package PhpWorkshop\PhpWorkshop\Check
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */

class FileExistsCheck implements CheckInterface
{

    /**
     * @param ExerciseInterface $exercise
     * @param string $fileName
     * @return Fail|Success
     */
    public function check(ExerciseInterface $exercise, $fileName)
    {
        if (file_exists($fileName)) {
            return new Success($exercise);
        }

        return new Fail($exercise, sprintf('File: "%s" does not exist', $fileName));
    }
}