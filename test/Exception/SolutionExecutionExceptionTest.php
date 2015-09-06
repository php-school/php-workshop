<?php

namespace PhpWorkshop\PhpWorkshopTest\Exception;

use PHPUnit_Framework_TestCase;
use PhpWorkshop\PhpWorkshop\Exception\SolutionExecutionException;

/**
 * Class SolutionExecutionExceptionTest
 * @package PhpWorkshop\PhpWorkshopTest\Exception
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class SolutionExecutionExceptionTest extends PHPUnit_Framework_TestCase
{
    public function testException()
    {
        $e = new SolutionExecutionException('nope');
        $this->assertEquals('nope', $e->getMessage());
    }
}
