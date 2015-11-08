<?php

namespace PhpSchool\PhpWorkshopTest\Result;

use PhpSchool\PhpWorkshop\Result\CgiOutBodyFailure;
use PHPUnit_Framework_TestCase;

/**
 * Class CgiOutBodyFailureTest
 * @package PhpSchool\PhpWorkshopTest\Result
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class CgiOutBodyFailureTest extends PHPUnit_Framework_TestCase
{
    public function testGetters()
    {
        $failure = new CgiOutBodyFailure('Expected Output', 'Actual Output');
        $this->assertEquals(
            'Output did not match. Expected: "Expected Output". Received: "Actual Output"',
            $failure->getReason()
        );

        $this->assertEquals('Expected Output', $failure->getExpectedOutput());
        $this->assertEquals('Actual Output', $failure->getActualOutput());
    }
}
