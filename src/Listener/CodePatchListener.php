<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Listener;

use PhpSchool\PhpWorkshop\CodePatcher;
use PhpSchool\PhpWorkshop\Event\EventInterface;
use PhpSchool\PhpWorkshop\Event\ExerciseRunnerEvent;
use PhpSchool\PhpWorkshop\Exercise\ProvidesSolution;
use PhpSchool\PhpWorkshop\Utils\Path;
use Psr\Log\LoggerInterface;

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
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var bool
     */
    private $debugMode;

    /**
     * @var array<string, string>
     */
    private $originalCode = [];

    /**
     * @param CodePatcher $codePatcher
     * @param LoggerInterface $logger
     * @param bool $debugMode
     */
    public function __construct(CodePatcher $codePatcher, LoggerInterface $logger, bool $debugMode)
    {
        $this->codePatcher = $codePatcher;
        $this->logger = $logger;
        $this->debugMode = $debugMode;
    }

    /**
     * Patches the student's solution and, if the exercise provides one, the reference solution.
     *
     * @param ExerciseRunnerEvent $event
     */
    public function patch(ExerciseRunnerEvent $event): void
    {
        $files = [$event->getContext()->getEntryPoint()];

        $exercise = $event->getExercise();
        if ($exercise instanceof ProvidesSolution) {
            $files[] = Path::join(
                $event->getContext()->getReferenceExecutionDirectory(),
                $exercise->getSolution()->getEntryPoint()->getRelativePath(),
            );
        }

        $this->patchFiles($event, $files);
    }

    /**
     * Patches only the student's solution. A run only executes the student's code, so the reference solution is
     * never copied and there is nothing else to patch.
     *
     * @param ExerciseRunnerEvent $event
     */
    public function patchStudent(ExerciseRunnerEvent $event): void
    {
        $this->patchFiles($event, [$event->getContext()->getEntryPoint()]);
    }

    /**
     * @param array<string> $files
     */
    private function patchFiles(ExerciseRunnerEvent $event, array $files): void
    {
        foreach (array_filter($files) as $fileName) {
            $this->logger->debug("Patching file: $fileName");

            $this->originalCode[$fileName] = (string) file_get_contents($fileName);

            file_put_contents(
                $fileName,
                $this->codePatcher->patch($event->getExercise(), $this->originalCode[$fileName]),
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

        //if we're in debug mode leave the students patch for debugging
        if ($event instanceof ExerciseRunnerEvent && $this->debugMode) {
            unset($this->originalCode[$event->getContext()->getEntryPoint()]);
        }

        foreach ($this->originalCode as $fileName => $contents) {
            file_put_contents($fileName, $contents);
            unset($this->originalCode[$fileName]);
        }
    }
}
