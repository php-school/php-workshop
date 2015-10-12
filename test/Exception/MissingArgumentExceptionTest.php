<?php

namespace PhpWorkshop\PhpWorkshopTest\Exception;

use PHPUnit_Framework_TestCase;
use PhpWorkshop\PhpWorkshop\Exception\MissingArgumentException;

/**
 * Class MissingArgumentExceptionTest
 * @package PhpWorkshop\PhpWorkshopTest\Exception
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class MissingArgumentExceptionTest extends PHPUnit_Framework_TestCase
{
    public function testException()
    {
        $e = new MissingArgumentException('some-route', ['arg1', 'arg2']);
        $this->assertEquals(
            'Command: "some-route" is missing the following arguments: "arg1", "arg2"',
            $e->getMessage()
        );
    }
}
