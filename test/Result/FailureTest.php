<?php

namespace PhpWorkshop\PhpWorkshopTest\Result;

use PHPUnit_Framework_TestCase;
use PhpWorkshop\PhpWorkshop\Result\ResultInterface;
use PhpWorkshop\PhpWorkshop\Result\Failure;

/**
 * Class FailureTest
 * @package PhpWorkshop\PhpWorkshopTest\Result
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class FailureTest extends PHPUnit_Framework_TestCase
{
    public function testSuccess()
    {
        $failure = new Failure('Something went wrong yo');
        $this->assertInstanceOf(ResultInterface::class, $failure);
        $this->assertEquals('Something went wrong yo', $failure->getReason());
    }
}
