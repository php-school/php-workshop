<?php

namespace PhpSchool\PhpWorkshopTest\Result;

use PhpSchool\PhpWorkshop\Result\CgiOutHeadersFailure;
use PHPUnit_Framework_TestCase;

/**
 * Class CgiOutHeadersFailureTest
 * @package PhpSchool\PhpWorkshopTest\Result
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class CgiOutHeadersFailureTest extends PHPUnit_Framework_TestCase
{
    public function testGetters()
    {
        $failure = new CgiOutHeadersFailure(['header1' => 'some-value'], ['header2' => 'some-value']);
        $this->assertEquals(
            'Headers did not match.',
            $failure->getReason()
        );

        $this->assertEquals(['header1' => 'some-value'], $failure->getExpectedHeaders());
        $this->assertEquals(['header2' => 'some-value'], $failure->getActualHeaders());
    }
}
