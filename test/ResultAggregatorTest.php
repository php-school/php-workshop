<?php


namespace PhpSchool\PhpWorkshopTest;

use PhpSchool\PhpWorkshop\Check\CheckInterface;
use PhpSchool\PhpWorkshopTest\Asset\ResultResultAggregator;
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
    /**
     * @var CheckInterface
     */
    private $check;

    public function setUp()
    {
        $this->check = $this->getMock(CheckInterface::class);
        $this->check
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('Some Check'));
    }
    
    public function testIsSuccessful()
    {
        $resultAggregator = new ResultAggregator;
        $this->assertTrue($resultAggregator->isSuccessful());

        $resultAggregator->add(new Success($this->check));
        $this->assertTrue($resultAggregator->isSuccessful());

        $resultAggregator->add(new Failure($this->check, 'nope'));
        $this->assertFalse($resultAggregator->isSuccessful());
    }

    public function testIsSuccessfulWithNestedResults()
    {
        $resultAggregator = new ResultAggregator;
        $this->assertTrue($resultAggregator->isSuccessful());
        
        $resultResultAggregator = new ResultResultAggregator;
        $resultResultAggregator->add(new Success($this->check));

        $resultAggregator->add($resultResultAggregator);
        $this->assertTrue($resultAggregator->isSuccessful());

        $resultResultAggregator->add(new Failure($this->check, 'nope'));

        $this->assertFalse($resultAggregator->isSuccessful());
    }

    public function testIterator()
    {
        $results = [
            new Success($this->check),
            new Failure($this->check, 'nope')
        ];
        
        $resultAggregator = new ResultAggregator;
        $resultAggregator->add($results[0]);
        $resultAggregator->add($results[1]);
        
        $this->assertEquals($results, iterator_to_array($resultAggregator));
    }
}
