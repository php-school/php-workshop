<?php

namespace PhpSchool\PhpWorkshopTest\Result;

use PhpSchool\PhpWorkshop\Check\CheckInterface;
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

    public function testSuccessFromCheck()
    {
        $check = $this->getMock(CheckInterface::class);
        $check
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('Some Check'));

        $success = Success::fromCheck($check);
        $this->assertInstanceOf(ResultInterface::class, $success);
        $this->assertEquals('Some Check', $success->getCheckName());
    }
}
