<?php

namespace PhpSchool\PhpWorkshopTest\Exception;

use PHPUnit_Framework_TestCase;
use PhpSchool\PhpWorkshop\Exception\MissingArgumentException;

/**
 * Class MissingArgumentExceptionTest
 * @package PhpSchool\PhpWorkshopTest\Exception
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

        $this->assertSame(['arg1', 'arg2'], $e->getMissingArguments());
    }
}
