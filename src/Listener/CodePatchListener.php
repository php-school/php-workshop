<?php

namespace PhpSchool\PhpWorkshop\Listener;

use PhpSchool\PhpWorkshop\CodePatcher;
use PhpSchool\PhpWorkshop\Event\Event;
use PhpSchool\PhpWorkshop\Event\ExerciseRunnerEvent;
use PhpSchool\PhpWorkshop\Input\Input;
use RuntimeException;

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
     * CodePatchListener constructor.
     * @param CodePatcher $codePatcher
     */
    public function __construct(CodePatcher $codePatcher)
    {
        $this->codePatcher = $codePatcher;
    }

    /**
     * @param ExerciseRunnerEvent $event
     */
    public function patch(ExerciseRunnerEvent $event)
    {
        $fileName = $event->getInput()->getArgument('program');

        $this->originalCode = file_get_contents($fileName);
        file_put_contents(
            $fileName,
            $this->codePatcher->patch($event->getExercise(), $this->originalCode)
        );
    }

    /**
     * @param ExerciseRunnerEvent $event
     */
    public function revert(ExerciseRunnerEvent $event)
    {
        if (null === $this->originalCode) {
            throw new RuntimeException('Can only revert previously patched code');
        }

        file_put_contents($event->getInput()->getArgument('program'), $this->originalCode);
    }
}
