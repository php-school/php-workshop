<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Listener;

use PhpSchool\PhpWorkshop\Event\Event;
use PhpSchool\PhpWorkshop\Exercise\ProvidesInitialCode;
use PhpSchool\PhpWorkshop\Solution\SolutionFile;

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

    public function __construct(string $workingDirectory)
    {
        $this->workingDirectory = $workingDirectory;
    }

    /**
     * @param Event $event
     */
    public function __invoke(Event $event): void
    {
        $exercise = $event->getParameter('exercise');

        if (!$exercise instanceof ProvidesInitialCode) {
            return;
        }

        foreach ($exercise->getInitialCode()->getFiles() as $file) {
            /** @var SolutionFile $file */
            if (!file_exists($this->workingDirectory . '/' . $file->getRelativePath())) {
                copy($file->getAbsolutePath(), $this->workingDirectory . '/' . $file->getRelativePath());
            }
        }
    }
}
