<?php

namespace PhpWorkshop\PhpWorkshop\Check;

use PhpWorkshop\PhpWorkshop\Exercise\ExerciseInterface;
use PhpWorkshop\PhpWorkshop\Result\Success;
use PhpWorkshop\PhpWorkshop\Result\Failure;
use Symfony\Component\Process\Process;

/**
 * Class PhpLintCheck
 * @package PhpWorkshop\PhpWorkshop\Check
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */

class PhpLintCheck implements CheckInterface
{

    /**
     * @param ExerciseInterface $exercise
     * @param string $fileName
     * @return Fail|Success
     */
    public function check(ExerciseInterface $exercise, $fileName)
    {
        $process = new Process(sprintf('%s -l %s', PHP_BINARY, $fileName));
        $process->run();

        if ($process->isSuccessful()) {
            return new Success;
        }

        return new Failure($process->getErrorOutput());
    }

    /**
     * @return bool
     */
    public function breakChainOnFailure()
    {
        return true;
    }
}
