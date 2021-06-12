<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Check;

use InvalidArgumentException;
use PhpSchool\PhpWorkshop\Exception\SolutionFileDoesNotExistException;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\Exercise\ExerciseType;
use PhpSchool\PhpWorkshop\Exercise\ProvidesSolution;
use PhpSchool\PhpWorkshop\ExerciseCheck\FileComparisonExerciseCheck;
use PhpSchool\PhpWorkshop\Input\Input;
use PhpSchool\PhpWorkshop\Result\Failure;
use PhpSchool\PhpWorkshop\Result\FileComparisonFailure;
use PhpSchool\PhpWorkshop\Result\ResultInterface;
use PhpSchool\PhpWorkshop\Result\Success;
use PhpSchool\PhpWorkshop\Utils\Path;

/**
 * This check verifies that any additional files which should be created by a student, match the ones
 * created by the reference solution.
 */
class FileComparisonCheck implements SimpleCheckInterface
{
    /**
     * Return the check's name.
     */
    public function getName(): string
    {
        return 'File Comparison Check';
    }

    /**
     * Simply check that the file exists.
     *
     * @param ExerciseInterface&ProvidesSolution $exercise The exercise to check against.
     * @param Input $input The command line arguments passed to the command.
     * @return ResultInterface The result of the check.
     */
    public function check(ExerciseInterface $exercise, Input $input): ResultInterface
    {
        if (!$exercise instanceof FileComparisonExerciseCheck) {
            throw new InvalidArgumentException();
        }

        foreach ($exercise->getFilesToCompare() as $file) {
            $studentFile = Path::join(dirname($input->getRequiredArgument('program')), $file);
            $referenceFile = Path::join($exercise->getSolution()->getBaseDirectory(), $file);

            if (!file_exists($referenceFile)) {
                throw SolutionFileDoesNotExistException::fromExpectedFile($file);
            }

            if (!file_exists($studentFile)) {
                return Failure::fromCheckAndReason($this, sprintf('File: "%s" does not exist', $file));
            }

            $actual = (string) file_get_contents($studentFile);
            $expected = (string) file_get_contents($referenceFile);

            if ($expected !== $actual) {
                return new FileComparisonFailure($this, $file, $expected, $actual);
            }
        }

        return Success::fromCheck($this);
    }

    /**
     * This check can run on any exercise type.
     *
     * @param ExerciseType $exerciseType
     * @return bool
     */
    public function canRun(ExerciseType $exerciseType): bool
    {
        return in_array($exerciseType->getValue(), [ExerciseType::CGI, ExerciseType::CLI], true);
    }

    public function getExerciseInterface(): string
    {
        return FileComparisonExerciseCheck::class;
    }

    /**
     * This check must run after executing the solution because the files will not exist otherwise.
     */
    public function getPosition(): string
    {
        return SimpleCheckInterface::CHECK_AFTER;
    }
}
