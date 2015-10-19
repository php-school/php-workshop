<?php

namespace PhpSchool\PhpWorkshopTest\Result;

use PHPUnit_Framework_TestCase;
use PhpSchool\PhpWorkshop\Result\ResultInterface;
use PhpSchool\PhpWorkshop\Result\Success;

/**
 * Class SuccessTest
 * @package PhpSchool\PhpWorkshopTest\Result
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class SuccessTest extends PHPUnit_Framework_TestCase
{
    public function testSuccess()
    {
        $success = new Success('Some Check');
        $this->assertInstanceOf(ResultInterface::class, $success);
        $this->assertEquals('Some Check', $success->getCheckName());
    }
}
