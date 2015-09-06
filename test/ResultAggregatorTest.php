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

        $resultAggregator->add(new Success);
        $this->assertTrue($resultAggregator->isSuccessful());

        $resultAggregator->add(new Failure('nope'));
        $this->assertFalse($resultAggregator->isSuccessful());
    }

    public function testGetErrors()
    {
        $resultAggregator = new ResultAggregator;
        $resultAggregator->add(new Success);
        $resultAggregator->add(new Failure('nope'));
        $resultAggregator->add(new Failure('so much nope'));

        $expected = ['nope','so much nope'];
        $this->assertEquals($expected, $resultAggregator->getErrors());
    }
}
