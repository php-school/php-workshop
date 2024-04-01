<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Listener;

use PhpSchool\PhpWorkshop\CodePatcher;
use PhpSchool\PhpWorkshop\Event\EventInterface;
use PhpSchool\PhpWorkshop\Event\ExerciseRunnerEvent;
use PhpSchool\PhpWorkshop\Exercise\ProvidesSolution;
use PhpSchool\PhpWorkshop\Utils\Path;
use Psr\Log\LoggerInterface;
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
     * @param ExerciseRunnerEvent $event
     */
    public function patch(ExerciseRunnerEvent $event): void
    {
        $files = [$event->context->getExecutionContext()->getStudentSolutionFilePath()];

        $exercise = $event->getExercise();
        if ($exercise instanceof ProvidesSolution) {
            $files[] = Path::join(
                $event->context->getExecutionContext()->referenceEnvironment->workingDirectory,
                $exercise->getSolution()->getEntryPoint()->getRelativePath()
            );
        }

        foreach (array_filter($files) as $fileName) {
            $this->logger->debug("Patching file: $fileName");

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

        //if we're in debug mode leave the students patch for debugging
        if ($event instanceof ExerciseRunnerEvent && $this->debugMode) {
            unset($this->originalCode[$event->context->getExecutionContext()->getStudentSolutionFilePath()]);
        }

        foreach ($this->originalCode as $fileName => $contents) {
            file_put_contents($fileName, $contents);
        }
    }
}
