<?php

namespace PhpWorkshop\PhpWorkshopTest\Result;

use PHPUnit_Framework_TestCase;
use PhpWorkshop\PhpWorkshop\Result\ResultInterface;
use PhpWorkshop\PhpWorkshop\Result\Success;

/**
 * Class SuccessTest
 * @package PhpWorkshop\PhpWorkshopTest\Result
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
