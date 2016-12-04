<?php

namespace PhpSchool\PhpWorkshopTest\Result\Cli;

use PhpSchool\PhpWorkshop\Result\Cli\Success;
use PhpSchool\PhpWorkshop\Utils\ArrayObject;
use PHPUnit_Framework_TestCase;

/**
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class SuccessTest extends PHPUnit_Framework_TestCase
{
    public function testSuccess()
    {
        $args = new ArrayObject;
        $success = new Success($args);
        $this->assertInstanceOf(Success::class, $success);
        $this->assertSame($args, $success->getArgs());
        $this->assertEquals('CLI Program Runner', $success->getCheckName());
    }
}
