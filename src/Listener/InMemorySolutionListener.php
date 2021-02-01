<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Listener;

use PhpSchool\PhpWorkshop\Event\ExerciseRunnerEvent;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Move solution to temp and replace program arg
 */
class InMemorySolutionListener
{
    /**
     * @param ExerciseRunnerEvent $event
     */
    public function __invoke(ExerciseRunnerEvent $event): void
    {
        if (!$event->getInput()->hasArgument('program')) {
            return;
        }

        $program = $event->getInput()->getRequiredArgument('program');

        if (!file_exists($program)) {
            return;
        }

        $filesystem = new Filesystem();
        $basedir = basename($program);
        $tmp = sprintf('%s/php-school/user-solution', sys_get_temp_dir());

        if ($filesystem->exists($tmp)) {
            $filesystem->remove($tmp);
        }

        $filesystem->mkdir($tmp);

        // Iterator with exclusions... TODO: Useful?
        $dirIterator = new \RecursiveDirectoryIterator($basedir, \RecursiveDirectoryIterator::SKIP_DOTS);
        $filter = new class($dirIterator) extends RecursiveFilterIterator {
            private const FILTERS = ['vendor', '.idea', ',git'];
            public function accept() {
                return !in_array($this->current()->getFilename(), self::FILTERS, true);
            }
        };
        $iterator = new \RecursiveIteratorIterator($filter, \RecursiveIteratorIterator::SELF_FIRST);

        $composerFile = sprintf('%s/composer.json', $basedir);
        if ($filesystem->exists($composerFile)) {
            $filesystem->copy($composerFile, $tmp . '/composer.json');
        }


        // TODO should this event be somewhere we have the exercise
        // TODO: We could then check if it's a single file solutino or dir solution
        // TODO: Single file solutions can be just copying entrypoint to temp
        // TODO: dir solution we can copy the dirname to temp, however we should take care here
        // TODO: e.g. what if it's all just in the root home dir... we can't recursively copy that all into temp...
        // TODO: how do we prevent such an issue ?!?


        $event->getInput()->setArgument('program', (string) realpath($program));
    }
}
