<?php

namespace PhpSchool\PhpWorkshop\ProcessExecutor;

/**
 * Interface ProcessExecutorInterface
 * @package PhpSchool\PhpWorkshop\ProcessExecutor
 * @author Michael Woodward <mikeymike.mw@gmail.com>
 */
interface ProcessExecutorInterface
{
    /**
     * Run the given PHP file
     *
     * @param $fileName
     * @return string
     */
    public function executePhpFile($fileName);
}
