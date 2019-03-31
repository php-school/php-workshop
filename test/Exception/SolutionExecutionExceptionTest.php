<?php

namespace PhpSchool\PhpWorkshopTest\Exception;

use PHPUnit\Framework\TestCase;
use PhpSchool\PhpWorkshop\Exception\SolutionExecutionException;

/**
 * Class SolutionExecutionExceptionTest
 * @package PhpSchool\PhpWorkshopTest\Exception
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class SolutionExecutionExceptionTest extends TestCase
{
    public function testException()
    {
        $e = new SolutionExecutionException('nope');
        $this->assertEquals('nope', $e->getMessage());
    }
}
