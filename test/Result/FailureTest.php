<?php

namespace PhpSchool\PhpWorkshopTest\Result;

use PHPUnit_Framework_TestCase;
use PhpSchool\PhpWorkshop\Result\ResultInterface;
use PhpSchool\PhpWorkshop\Result\Failure;

/**
 * Class FailureTest
 * @package PhpSchool\PhpWorkshopTest\Result
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class FailureTest extends PHPUnit_Framework_TestCase
{
    public function testSuccess()
    {
        $failure = new Failure('Some Check', 'Something went wrong yo');
        $this->assertInstanceOf(ResultInterface::class, $failure);
        $this->assertEquals('Something went wrong yo', $failure->getReason());
        $this->assertEquals('Some Check', $failure->getCheckName());
    }
}
