<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Listener;

use PhpSchool\PhpWorkshop\Event\Event;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\Exercise\ProvidesInitialCode;
use Psr\Log\LoggerInterface;

/**
 * Copy over any initial files for this exercise when
 * it is selected in the menu - only if they do not exist already
 *
 * We might want to ask the user to force this if the files exist, we could also check
 * the contents match what we expect.
 */
class InitialCodeListener
{
    /**
     * @var string
     */
    private $workingDirectory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(string $workingDirectory, LoggerInterface $logger)
    {
        $this->workingDirectory = $workingDirectory;
        $this->logger = $logger;
    }

    /**
     * @param Event $event
     */
    public function __invoke(Event $event): void
    {
        /** @var ExerciseInterface $exercise */
        $exercise = $event->getParameter('exercise');

        if (!$exercise instanceof ProvidesInitialCode) {
            return;
        }

        foreach ($exercise->getInitialCode()->getFiles() as $file) {
            if (!file_exists($this->workingDirectory . '/' . $file->getRelativePath())) {
                copy($file->getAbsolutePath(), $this->workingDirectory . '/' . $file->getRelativePath());
                $message = 'File successfully copied to working directory';
            } else {
                $message = 'File not copied. File with same name already exists in working directory';
            }

            $this->logger->debug(
                $message,
                [
                    'exercise' => $exercise->getName(),
                    'workingDir' => $this->workingDirectory,
                    'file' => $file->getAbsolutePath(),
                ],
            );
        }
    }
}
