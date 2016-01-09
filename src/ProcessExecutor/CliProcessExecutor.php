<?php

namespace PhpSchool\PhpWorkshop\ProcessExecutor;

use PhpSchool\PhpWorkshop\Exception\CodeExecutionException;
use Symfony\Component\Process\Process;

/**
 * Class CliProcessExecutor
 * @package PhpSchool\PhpWorkshop\ProcessExecutor
 * @author Michael Woodward <mikeymike.mw@gmail.com>
 */
class CliProcessExecutor implements ProcessExecutorInterface
{
    /**
     * @var array
     */
    private $args;

    /**
     * @param array $args
     */
    public function __construct(array $args)
    {
        $this->args = array_map('escapeshellarg', $args);
    }

    /**
     * Run the given PHP file
     *
     * @param string $fileName
     * @return string
     */
    public function executePhpFile($fileName)
    {
        $cmd     = sprintf('%s %s %s', PHP_BINARY, $fileName, implode(' ', $this->args));
        $process = new Process($cmd, dirname($fileName));

        $process->run();

        if (!$process->isSuccessful()) {
            throw CodeExecutionException::fromProcess($process);
        }

        return $process->getOutput();
    }
}