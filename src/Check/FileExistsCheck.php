<?php

namespace PhpSchool\PhpWorkshop\Check;

use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\Result\Failure;
use PhpSchool\PhpWorkshop\Result\ResultInterface;
use PhpSchool\PhpWorkshop\Result\Success;

/**
 * Class FileExistsCheck
 * @package PhpSchool\PhpWorkshop\Check
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class FileExistsCheck implements CheckInterface
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'File Exists Check';
    }

    /**
     * @param ExerciseInterface $exercise
     * @param string $fileName
     * @return ResultInterface
     */
    public function check(ExerciseInterface $exercise, $fileName)
    {
        if (file_exists($fileName)) {
            return new Success($this);
        }

        return Failure::withReason($this, sprintf('File: "%s" does not exist', $fileName));
    }

    /**
     * @return bool
     */
    public function breakChainOnFailure()
    {
        return true;
    }
}
