<?php

namespace PhpSchool\PhpWorkshop\ProcessExecutor;

use Zend\Diactoros\Response;

/**
 * Interface ProcessExecutorInterface
 * @package PhpSchool\PhpWorkshop\ProcessExecutor
 * @author Michael Woodward <mikeymike.mw@gmail.com>
 */
interface CgiProcessExecutorInterface extends ProcessExecutorInterface
{
    /**
     * Run the given PHP file returning a Response object
     *
     * @param $fileName
     * @return Response
     */
    public function asResponseObject($fileName);
}
