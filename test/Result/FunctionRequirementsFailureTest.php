<?php

namespace PhpSchool\PhpWorkshopTest\Result;

use PhpSchool\PhpWorkshop\Check\CheckInterface;
use PHPUnit\Framework\TestCase;
use PhpSchool\PhpWorkshop\Result\FunctionRequirementsFailure;

/**
 * Class FunctionRequirementsFailureTest
 * @package PhpSchool\PhpWorkshopTest\Result
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class FunctionRequirementsFailureTest extends TestCase
{
    public function testGetters(): void
    {
        $check = $this->createMock(CheckInterface::class);
        $check
            ->method('getName')
            ->willReturn('Some Check');
        
        $failure = new FunctionRequirementsFailure($check, ['function' => 'file', 'line' => 3], ['explode']);
        $this->assertEquals(['function' => 'file', 'line' => 3], $failure->getBannedFunctions());
        $this->assertEquals(['explode'], $failure->getMissingFunctions());
        $this->assertSame('Some Check', $failure->getCheckName());
    }
}
