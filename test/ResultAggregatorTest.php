<?php


namespace PhpWorkshop\PhpWorkshopTest;

use PHPUnit_Framework_TestCase;
use PhpWorkshop\PhpWorkshop\Result\Failure;
use PhpWorkshop\PhpWorkshop\Result\Success;
use PhpWorkshop\PhpWorkshop\ResultAggregator;

/**
 * Class ResultAggregatorTest
 * @package PhpWorkshop\PhpWorkshopTest
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class ResultAggregatorTest extends PHPUnit_Framework_TestCase
{
    public function testIsSuccessful()
    {
        $resultAggregator = new ResultAggregator;
        $this->assertTrue($resultAggregator->isSuccessful());

        $resultAggregator->add(new Success('Some Check'));
        $this->assertTrue($resultAggregator->isSuccessful());

        $resultAggregator->add(new Failure('Some Check', 'nope'));
        $this->assertFalse($resultAggregator->isSuccessful());
    }

    public function testGetErrors()
    {
        $resultAggregator = new ResultAggregator;
        $resultAggregator->add(new Success('Some Check'));
        $resultAggregator->add(new Failure('Some Check', 'nope'));
        $resultAggregator->add(new Failure('Some Check', 'so much nope'));

        $expected = ['nope','so much nope'];
        $this->assertEquals($expected, $resultAggregator->getErrors());
    }

    public function testIterator()
    {
        $results = [
            new Success('Some Check'),
            new Failure('Some Check', 'nope')
        ];
        
        $resultAggregator = new ResultAggregator;
        $resultAggregator->add($results[0]);
        $resultAggregator->add($results[1]);
        
        $this->assertEquals($results, iterator_to_array($resultAggregator));
    }
}
