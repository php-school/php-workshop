<?php

namespace PhpWorkshop\PhpWorkshop\Check;

use PhpWorkshop\PhpWorkshop\Exercise\ExerciseInterface;
use PhpWorkshop\PhpWorkshop\Result\Failure;
use PhpWorkshop\PhpWorkshop\Result\ResultInterface;
use PhpWorkshop\PhpWorkshop\Result\Success;

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
     * @return ResultInterface
     */
    public function check(ExerciseInterface $exercise, $fileName)
    {
        if (file_exists($fileName)) {
            return new Success;
        }

        return new Failure(sprintf('File: "%s" does not exist', $fileName));
    }

    /**
     * @return bool
     */
    public function breakChainOnFailure()
    {
        return true;
    }
}
