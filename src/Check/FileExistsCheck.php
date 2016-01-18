<?php

namespace PhpSchool\PhpWorkshop\Check;

use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\Exercise\ExerciseType;
use PhpSchool\PhpWorkshop\Result\Failure;
use PhpSchool\PhpWorkshop\Result\ResultInterface;
use PhpSchool\PhpWorkshop\Result\Success;

/**
 * Class FileExistsCheck
 * @package PhpSchool\PhpWorkshop\Check
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class FileExistsCheck implements SimpleCheckInterface
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
            return Success::fromCheck($this);
        }

        return Failure::fromCheckAndReason($this, sprintf('File: "%s" does not exist', $fileName));
    }

    /**
     * @param ExerciseType $exerciseType
     * @return bool
     */
    public function canRun(ExerciseType $exerciseType)
    {
        return true;
    }

    /**
     *
     * @return string
     */
    public function getExerciseInterface()
    {
        return ExerciseInterface::class;
    }
}
