<?php

namespace PhpSchool\PhpWorkshopTest\Result;

use PhpSchool\PhpWorkshop\Check\CheckInterface;
use PHPUnit_Framework_TestCase;
use PhpSchool\PhpWorkshop\Result\FunctionRequirementsFailure;

/**
 * Class FunctionRequirementsFailureTest
 * @package PhpSchool\PhpWorkshopTest\Result
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class FunctionRequirementsFailureTest extends PHPUnit_Framework_TestCase
{
    public function testGetters()
    {
        $check = $this->getMock(CheckInterface::class);
        $check
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('Some Check'));
        
        $failure = new FunctionRequirementsFailure($check, ['function' => 'file', 'line' => 3], ['explode']);
        $this->assertEquals(['function' => 'file', 'line' => 3], $failure->getBannedFunctions());
        $this->assertEquals(['explode'], $failure->getMissingFunctions());
        $this->assertSame('Some Check', $failure->getCheckName());
    }
}
