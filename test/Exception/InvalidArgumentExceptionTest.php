<?php

namespace PhpSchool\PhpWorkshopTest\Exception;

use PhpSchool\PhpWorkshop\Exception\InvalidArgumentException;
use PHPUnit_Framework_TestCase;

/**
 * Class InvalidArgumentExceptionTest
 * @package PhpSchool\PhpWorkshopTest\Exception
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class InvalidArgumentExceptionTest extends PHPUnit_Framework_TestCase
{
    public function testException()
    {
        $e = new InvalidArgumentException('nope');
        $this->assertEquals('nope', $e->getMessage());
    }

    public function testExceptionFromStaticConstructor()
    {
        $e = InvalidArgumentException::typeMisMatch('string', new \stdClass);
        $this->assertEquals('Expected: "string" Received: "stdClass"', $e->getMessage());
    }
}
