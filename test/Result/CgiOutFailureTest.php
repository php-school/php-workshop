<?php

namespace PhpSchool\PhpWorkshopTest\Result;

use PhpSchool\PhpWorkshop\Check\CheckInterface;
use PhpSchool\PhpWorkshop\Result\CgiOutBodyFailure;
use PhpSchool\PhpWorkshop\Result\CgiOutFailure;
use PHPUnit_Framework_TestCase;

/**
 * Class CgiOutFailureTestA
 * @package PhpSchool\PhpWorkshopTest\Result
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class CgiOutFailureTest extends PHPUnit_Framework_TestCase
{
    public function testOutputGetters()
    {
        $check = $this->getMock(CheckInterface::class);
        $check
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('Some Check'));

        $failure = new CgiOutFailure($check, 'Expected Output', 'Actual Output', [], []);
        
        $this->assertEquals('Expected Output', $failure->getExpectedOutput());
        $this->assertEquals('Actual Output', $failure->getActualOutput());
    }

    public function testHeaderGetters()
    {
        $check = $this->getMock(CheckInterface::class);
        $check
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('Some Check'));
        
        $failure = new CgiOutFailure(
            $check,
            'Output',
            'Output',
            ['header1' => 'some-value'],
            ['header2' => 'some-value']
        );

        $this->assertEquals(['header1' => 'some-value'], $failure->getExpectedHeaders());
        $this->assertEquals(['header2' => 'some-value'], $failure->getActualHeaders());
    }
}
