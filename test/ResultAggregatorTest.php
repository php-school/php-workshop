<?php


namespace PhpSchool\PhpWorkshopTest;

use PhpSchool\PhpWorkshop\Check\CheckInterface;
use PHPUnit_Framework_TestCase;
use PhpSchool\PhpWorkshop\Result\Failure;
use PhpSchool\PhpWorkshop\Result\Success;
use PhpSchool\PhpWorkshop\ResultAggregator;

/**
 * Class ResultAggregatorTest
 * @package PhpSchool\PhpWorkshopTest
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class ResultAggregatorTest extends PHPUnit_Framework_TestCase
{
    public function testIsSuccessful()
    {
        $resultAggregator = new ResultAggregator;
        $this->assertTrue($resultAggregator->isSuccessful());

        $check = $this->getMock(CheckInterface::class);
        $check
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('Some Check'));

        $resultAggregator->add(new Success($check));
        $this->assertTrue($resultAggregator->isSuccessful());

        $resultAggregator->add(new Failure($check, 'nope'));
        $this->assertFalse($resultAggregator->isSuccessful());
    }

    public function testGetErrors()
    {
        $check = $this->getMock(CheckInterface::class);
        $check
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('Some Check'));
        
        $resultAggregator = new ResultAggregator;
        $resultAggregator->add(new Success($check));
        $resultAggregator->add(new Failure($check, 'nope'));
        $resultAggregator->add(new Failure($check, 'so much nope'));

        $expected = ['nope','so much nope'];
        $this->assertEquals($expected, $resultAggregator->getErrors());
    }

    public function testIterator()
    {
        $check = $this->getMock(CheckInterface::class);
        $check
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('Some Check'));


        $results = [
            new Success($check),
            new Failure($check, 'nope')
        ];
        
        $resultAggregator = new ResultAggregator;
        $resultAggregator->add($results[0]);
        $resultAggregator->add($results[1]);
        
        $this->assertEquals($results, iterator_to_array($resultAggregator));
    }
}
