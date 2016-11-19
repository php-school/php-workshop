<?php

namespace PhpSchool\PhpWorkshop\Check;

use PhpSchool\PhpWorkshop\ComposerUtil\LockFileParser;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\Exercise\ExerciseType;
use PhpSchool\PhpWorkshop\ExerciseCheck\ComposerExerciseCheck;
use PhpSchool\PhpWorkshop\Input\Input;
use PhpSchool\PhpWorkshop\Result\Failure;
use PhpSchool\PhpWorkshop\Result\ResultInterface;
use PhpSchool\PhpWorkshop\Result\Success;

/**
 * This check looks for a set of composer packages specified by the exercise
 * in the students `composer.lock` file.
 *
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class ComposerCheck implements SimpleCheckInterface
{

    /**
     * Return the check's name
     *
     * @return string
     */
    public function getName()
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
     */
    public function check(ExerciseInterface $exercise, Input $input)
    {
        if (!$exercise instanceof ComposerExerciseCheck) {
            throw new \InvalidArgumentException;
        }
        
        if (!file_exists(sprintf('%s/composer.json', dirname($input->getArgument('program'))))) {
            return new Failure($this->getName(), 'No composer.json file found');
        }

        if (!file_exists(sprintf('%s/composer.lock', dirname($input->getArgument('program'))))) {
            return new Failure($this->getName(), 'No composer.lock file found');
        }
        
        $lockFile = new LockFileParser(sprintf('%s/composer.lock', dirname($input->getArgument('program'))));
        $missingPackages = array_filter($exercise->getRequiredPackages(), function ($package) use ($lockFile) {
            return !$lockFile->hasInstalledPackage($package);
        });
        
        if (count($missingPackages) > 0) {
            return new Failure(
                $this->getName(),
                sprintf(
                    'Lockfile doesn\'t include the following packages at any version: "%s"',
                    implode('", "', $missingPackages)
                )
            );
        }

        return new Success($this->getName());
    }

    /**
     * This check can run on any exercise type.
     *
     * @param ExerciseType $exerciseType
     * @return bool
     */
    public function canRun(ExerciseType $exerciseType)
    {
        return in_array($exerciseType->getValue(), [ExerciseType::CGI, ExerciseType::CLI]);
    }

    /**
     *
     * @return string
     */
    public function getExerciseInterface()
    {
        return ComposerExerciseCheck::class;
    }

    /**
     * This check can run before because if it fails, there is no point executing the solution.
     *
     * @return string
     */
    public function getPosition()
    {
        return SimpleCheckInterface::CHECK_BEFORE;
    }
}
