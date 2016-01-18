<?php

namespace PhpSchool\PhpWorkshop\Check;

use PhpSchool\PhpWorkshop\ComposerUtil\LockFileParser;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\Exercise\ExerciseType;
use PhpSchool\PhpWorkshop\ExerciseCheck\ComposerExerciseCheck;
use PhpSchool\PhpWorkshop\Result\Failure;
use PhpSchool\PhpWorkshop\Result\ResultInterface;
use PhpSchool\PhpWorkshop\Result\Success;

/**
 * Class ComposerCheck
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class ComposerCheck implements SimpleCheckInterface
{

    /**
     * @return string
     */
    public function getName()
    {
        return 'Composer Dependency Check';
    }

    /**
     * @param ExerciseInterface $exercise
     * @param string $fileName
     * @return ResultInterface
     */
    public function check(ExerciseInterface $exercise, $fileName)
    {
        if (!$exercise instanceof ComposerExerciseCheck) {
            throw new \InvalidArgumentException;
        }
        
        if (!file_exists(sprintf('%s/composer.json', dirname($fileName)))) {
            return new Failure($this->getName(), 'No composer.json file found');
        }

        if (!file_exists(sprintf('%s/composer.lock', dirname($fileName)))) {
            return new Failure($this->getName(), 'No composer.lock file found');
        }
        
        $lockFile = new LockFileParser(sprintf('%s/composer.lock', dirname($fileName)));
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
        return ComposerExerciseCheck::class;
    }
}
