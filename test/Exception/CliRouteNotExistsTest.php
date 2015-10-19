<?php

namespace PhpSchool\PhpWorkshopTest\Exception;

use PHPUnit_Framework_TestCase;
use PhpSchool\PhpWorkshop\Exception\CliRouteNotExistsException;

/**
 * Class CliRouteNotExistsTest
 * @package PhpSchool\PhpWorkshopTest\Exception
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class CliRouteNotExistsTest extends PHPUnit_Framework_TestCase
{
    public function testException()
    {
        $e = new CliRouteNotExistsException('some-route');
        $this->assertEquals('Command: "some-route" does not exist', $e->getMessage());
    }
}
