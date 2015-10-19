<?php

namespace PhpSchool\PhpWorkshopTest\Result;

use PHPUnit_Framework_TestCase;
use PhpSchool\PhpWorkshop\Result\StdOutFailure;

/**
 * Class StdOutFailureTest
 * @package PhpSchool\PhpWorkshopTest\Result
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class StdOutFailureTest extends PHPUnit_Framework_TestCase
{
    public function testGetters()
    {
        $failure = new StdOutFailure('Expected Output', 'Actual Output');
        $this->assertEquals(
            'Output did not match. Expected: "Expected Output". Received: "Actual Output"',
            $failure->getReason()
        );

        $this->assertEquals('Expected Output', $failure->getExpectedOutput());
        $this->assertEquals('Actual Output', $failure->getActualOutput());
    }
}
