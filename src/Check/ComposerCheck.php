<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Check;

use InvalidArgumentException;
use PhpSchool\PhpWorkshop\ComposerUtil\LockFileParser;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\Exercise\ExerciseType;
use PhpSchool\PhpWorkshop\ExerciseCheck\ComposerExerciseCheck;
use PhpSchool\PhpWorkshop\Input\Input;
use PhpSchool\PhpWorkshop\Result\ComposerFailure;
use PhpSchool\PhpWorkshop\Result\Failure;
use PhpSchool\PhpWorkshop\Result\ResultInterface;
use PhpSchool\PhpWorkshop\Result\Success;

/**
 * This check looks for a set of composer packages specified by the exercise
 * in the students `composer.lock` file.
 */
class ComposerCheck implements SimpleCheckInterface
{
    /**
     * Return the check's name
     */
    public function getName(): string
    {
        return 'Composer Dependency Check';
    }

    /**
     * This check parses the `composer.lock` file and checks that the student
     * installed a set of required packages. If they did not a failure is returned, otherwise,
     * a success is returned.
     *
     * @param ExerciseInterface $exercise The exercise to check against.
     * @param Input $input The command line arguments passed to the command.
     * @return ResultInterface The result of the check.
     * @noinspection SpellCheckingInspection
     */
    public function check(ExerciseInterface $exercise, Input $input): ResultInterface
    {
        if (!$exercise instanceof ComposerExerciseCheck) {
            throw new InvalidArgumentException();
        }

        if (!file_exists(sprintf('%s/composer.json', dirname($input->getRequiredArgument('program'))))) {
            return ComposerFailure::fromCheckAndMissingFileOrFolder($this, 'composer.json');
        }

        if (!file_exists(sprintf('%s/composer.lock', dirname($input->getRequiredArgument('program'))))) {
            return ComposerFailure::fromCheckAndMissingFileOrFolder($this, 'composer.lock');
        }

        if (!file_exists(sprintf('%s/vendor', dirname($input->getRequiredArgument('program'))))) {
            return ComposerFailure::fromCheckAndMissingFileOrFolder($this, 'vendor');
        }

        $lockFile = new LockFileParser(sprintf('%s/composer.lock', dirname($input->getRequiredArgument('program'))));
        $missingPackages = array_filter($exercise->getRequiredPackages(), function ($package) use ($lockFile) {
            return !$lockFile->hasInstalledPackage($package);
        });

        if (count($missingPackages) > 0) {
            return ComposerFailure::fromCheckAndMissingPackages($this, $missingPackages);
        }

        return new Success($this->getName());
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
        return ComposerExerciseCheck::class;
    }

    /**
     * This check can run before because if it fails, there is no point executing the solution.
     */
    public function getPosition(): string
    {
        return SimpleCheckInterface::CHECK_BEFORE;
    }
}
