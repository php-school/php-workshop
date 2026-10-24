<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Listener;

use PhpSchool\PhpWorkshop\Event\ExerciseRunnerEvent;

/**
 * Deletes every symlink in the student's directory once their code has run.
 *
 * Student code can replace its own files with links, eg `solution.php -> /var/www/app/.env`. The host then reads and
 * writes those files (reverting the code patch, comparing files), which would follow the link out of the directory.
 */
class RemoveStudentSymlinksListener
{
    public function __invoke(ExerciseRunnerEvent $event): void
    {
        $directory = $event->getContext()->getStudentExecutionDirectory();

        if (!is_dir($directory)) {
            return;
        }

        //does not descend into linked directories, so this only ever sees what is inside the student's directory
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST,
        );

        $links = [];
        /** @var \SplFileInfo $file */
        foreach ($files as $file) {
            if ($file->isLink()) {
                $links[] = $file->getPathname();
            }
        }

        foreach ($links as $link) {
            unlink($link);
        }
    }
}
