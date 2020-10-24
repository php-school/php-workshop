<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Listener;

use PhpSchool\PhpWorkshop\CodePatcher;
use PhpSchool\PhpWorkshop\Event\Event;
use PhpSchool\PhpWorkshop\Event\ExerciseRunnerEvent;
use PhpSchool\PhpWorkshop\Input\Input;
use RuntimeException;

/**
 * Listener which patches student's solutions
 */
class CodePatchListener
{
    /**
     * @var CodePatcher
     */
    private $codePatcher;

    /**
     * @var string
     */
    private $originalCode;

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
        $fileName = $event->getInput()->getArgument('program');

        if (null === $fileName) {
            return;
        }

        $this->originalCode = (string) file_get_contents($fileName);
        file_put_contents(
            $fileName,
            $this->codePatcher->patch($event->getExercise(), $this->originalCode)
        );
    }

    /**
     * @param ExerciseRunnerEvent $event
     */
    public function revert(ExerciseRunnerEvent $event): void
    {
        if (null === $this->originalCode) {
            throw new RuntimeException('Can only revert previously patched code');
        }

        $fileName = $event->getInput()->getArgument('program');

        if (null === $fileName) {
            return;
        }

        file_put_contents($fileName, $this->originalCode);
    }
}
