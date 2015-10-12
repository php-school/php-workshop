<?php

namespace PhpWorkshop\PhpWorkshopTest\Result;

use PHPUnit_Framework_TestCase;
use PhpWorkshop\PhpWorkshop\Result\FunctionRequirementsFailure;

/**
 * Class FunctionRequirementsFailureTest
 * @package PhpWorkshop\PhpWorkshopTest\Result
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class FunctionRequirementsFailureTest extends PHPUnit_Framework_TestCase
{
    public function testGetters()
    {
        $failure = new FunctionRequirementsFailure(['function' => 'file', 'line' => 3], ['explode']);
        $this->assertEquals('Function Requirements were not met', $failure->getReason());
        $this->assertEquals(['function' => 'file', 'line' => 3], $failure->getBannedFunctions());
        $this->assertEquals(['explode'], $failure->getMissingFunctions());
    }
}
