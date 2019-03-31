<?php


namespace PhpSchool\PhpWorkshopTest;

use PhpSchool\PhpWorkshop\Check\CheckInterface;
use PhpSchool\PhpWorkshop\Result\Cli\CliResult;
use PhpSchool\PhpWorkshop\Utils\ArrayObject;
use PHPUnit\Framework\TestCase;
use PhpSchool\PhpWorkshop\Result\Failure;
use PhpSchool\PhpWorkshop\Result\Success;
use PhpSchool\PhpWorkshop\Result\Cli\Success as CliSuccess;
use PhpSchool\PhpWorkshop\Result\Cli\GenericFailure as CliGenericFailure;
use PhpSchool\PhpWorkshop\ResultAggregator;

/**
 * Class ResultAggregatorTest
 * @package PhpSchool\PhpWorkshopTest
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class ResultAggregatorTest extends TestCase
{
    /**
     * @var CheckInterface
     */
    private $check;

    public function setUp()
    {
        $this->check = $this->createMock(CheckInterface::class);
        $this->check
            ->method('getName')
            ->willReturn('Some Check');
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

    public function testIsSuccessfulWithResultGroups()
    {
        $resultAggregator = new ResultAggregator;
        $this->assertTrue($resultAggregator->isSuccessful());
        
        $resultGroup = new CliResult;
        $resultGroup->add(new CliSuccess(new ArrayObject));

        $resultAggregator->add($resultGroup);
        $this->assertTrue($resultAggregator->isSuccessful());

        $resultGroup->add(new CliGenericFailure(new ArrayObject, 'nop'));

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
