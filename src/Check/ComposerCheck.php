<?php

namespace PhpSchool\PhpWorkshop\Check;

use Composer\Config;
use Composer\Installer\InstallationManager;
use Composer\IO\NullIO;
use Composer\Json\JsonFile;
use Composer\Package\Locker;
use Composer\Repository\RepositoryManager;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\ExerciseCheck\ComposerExerciseCheck;
use PhpSchool\PhpWorkshop\Result\Failure;
use PhpSchool\PhpWorkshop\Result\ResultInterface;
use PhpSchool\PhpWorkshop\Result\Success;

/**
 * Class ComposerCheck
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class ComposerCheck implements CheckInterface
{

    /**
     * @return string
     */
    public function getName()
    {
        return 'Composer Dependency Checker';
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

        $composerFile = new JsonFile(sprintf('%s/composer.json', dirname($fileName)));
        $lockFile     = new JsonFile(sprintf('%s/composer.lock', dirname($fileName)));

        if (!$composerFile->exists()) {
            return new Failure($this->getName(), 'No composer.json file found');
        }

        if (!$lockFile->exists()) {
            return new Failure($this->getName(), 'No composer.lock file found');
        }

        $locker = new Locker(
            new NullIO,
            $lockFile,
            new RepositoryManager(new NullIO, new Config),
            new InstallationManager,
            file_get_contents($composerFile->getPath())
        );
        
        $repository = $locker->getLockedRepository();
        
        $missingPackages = array_filter($exercise->getRequiredPackages(), function ($package) use ($repository) {
           return !$repository->findPackages($package);
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
}
