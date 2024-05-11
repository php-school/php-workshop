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

class ResultAggregatorTest extends TestCase
{
    /**
     * @var CheckInterface
     */
    private $check;

    public function setUp(): void
    {
        $this->check = $this->createMock(CheckInterface::class);
        $this->check
            ->method('getName')
            ->willReturn('Some Check');
    }

    public function testIsSuccessful(): void
    {
        $resultAggregator = new ResultAggregator();
        $this->assertTrue($resultAggregator->isSuccessful());

        $resultAggregator->add(Success::fromCheck($this->check));
        $this->assertTrue($resultAggregator->isSuccessful());

        $resultAggregator->add(Failure::fromCheckAndReason($this->check, 'nope'));
        $this->assertFalse($resultAggregator->isSuccessful());
    }

    public function testIsSuccessfulWithResultGroups(): void
    {
        $resultAggregator = new ResultAggregator();
        $this->assertTrue($resultAggregator->isSuccessful());

        $resultGroup = new CliResult();
        $resultGroup->add(new CliSuccess(new ArrayObject()));

        $resultAggregator->add($resultGroup);
        $this->assertTrue($resultAggregator->isSuccessful());

        $resultGroup->add(new CliGenericFailure(new ArrayObject(), 'nop'));

        $this->assertFalse($resultAggregator->isSuccessful());
    }

    public function testIterator(): void
    {
        $results = [
            new Success('Some Check'),
            new Failure('Some Check', 'nope'),
        ];

        $resultAggregator = new ResultAggregator();
        $resultAggregator->add($results[0]);
        $resultAggregator->add($results[1]);

        $this->assertEquals($results, iterator_to_array($resultAggregator));
    }
}
