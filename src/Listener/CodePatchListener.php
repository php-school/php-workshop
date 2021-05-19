<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Listener;

use PhpSchool\PhpWorkshop\CodePatcher;
use PhpSchool\PhpWorkshop\Event\EventInterface;
use PhpSchool\PhpWorkshop\Event\ExerciseRunnerEvent;
use PhpSchool\PhpWorkshop\Exercise\ProvidesSolution;
use RuntimeException;

/**
 * Listener which patches internal and student's solutions
 */
class CodePatchListener
{
    /**
     * @var CodePatcher
     */
    private $codePatcher;

    /**
     * @var array<string, string>
     */
    private $originalCode = [];

    /**
     * @param CodePatcher $codePatcher
     */
    public function __construct(CodePatcher $codePatcher)
    {
        $this->codePatcher = $codePatcher;
    }

    /**
     * @param ExerciseRunnerEvent $event
     */
    public function patch(ExerciseRunnerEvent $event): void
    {
        $files = [$event->getInput()->getArgument('program')];

        $exercise = $event->getExercise();
        if ($exercise instanceof ProvidesSolution) {
            $files[] = $exercise->getSolution()->getEntryPoint();
        }

        foreach (array_filter($files) as $fileName) {
            $this->originalCode[$fileName] = (string) file_get_contents($fileName);

            file_put_contents(
                $fileName,
                $this->codePatcher->patch($event->getExercise(), $this->originalCode[$fileName])
            );
        }
    }

    /**
     * @param EventInterface $event
     */
    public function revert(EventInterface $event): void
    {
        if (null === $this->originalCode || empty($this->originalCode)) {
            return;
        }

        foreach ($this->originalCode as $fileName => $contents) {
            file_put_contents($fileName, $contents);
        }
    }
}
