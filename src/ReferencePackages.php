<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop;

use PhpSchool\PhpWorkshop\Exception\RuntimeException;
use PhpSchool\PhpWorkshop\Exercise\ProvidesSolution;
use PhpSchool\PhpWorkshop\Process\ProcessInput;
use PhpSchool\PhpWorkshop\Process\Sandbox;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Locks and caches the packages of each reference solution, so a verify can install them with no network.
 * Run it on install/deploy, like the runtime image build.
 */
class ReferencePackages
{
    private Filesystem $filesystem;

    public function __construct(private ExerciseRepository $exerciseRepository, private Sandbox $sandbox)
    {
        $this->filesystem = new Filesystem();
    }

    public function warm(): void
    {
        foreach ($this->exerciseRepository->findAll() as $exercise) {
            if (!$exercise instanceof ProvidesSolution || !$exercise->getSolution()->hasComposerFile()) {
                continue;
            }

            $directory = $exercise->getSolution()->getBaseDirectory();

            //an old vendor folder would mean nothing is downloaded, so nothing is cached
            $this->filesystem->remove($directory . '/vendor');

            //keeps a committed composer.lock, or writes one next to the solution. It is copied with it on each verify
            $execution = $this->sandbox->create(
                new ProcessInput('composer', ['install', '--no-interaction'], $directory, [], []),
            );

            try {
                $execution->mustRun();
            } catch (\Symfony\Component\Process\Exception\RuntimeException $e) {
                throw new RuntimeException(sprintf('Composer packages for "%s" could not be installed', $exercise->getName()), 0, $e);
            }

            //verifies install from the cache, this copy is never used
            $this->filesystem->remove($directory . '/vendor');
        }
    }
}
