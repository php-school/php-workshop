<?php

namespace PhpWorkshop\PhpWorkshopTest\Exception;

use PHPUnit_Framework_TestCase;
use PhpWorkshop\PhpWorkshop\Exception\CliRouteNotExistsException;

/**
 * Class CliRouteNotExistsTest
 * @package PhpWorkshop\PhpWorkshopTest\Exception
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
