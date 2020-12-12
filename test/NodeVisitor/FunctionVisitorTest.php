<?php

namespace PhpSchool\PhpWorkshopTest\NodeVisitor;

use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PHPUnit\Framework\TestCase;
use PhpSchool\PhpWorkshop\NodeVisitor\FunctionVisitor;

class FunctionVisitorTest extends TestCase
{
    public function testLeaveNodeWithARequiredFunctionIsRecorded(): void
    {
        $node = new FuncCall(new Name('file_get_contents'));
        $visitor = new FunctionVisitor(['file_get_contents'], []);
        $visitor->leaveNode($node);

        $this->assertSame([$node], $visitor->getRequiredUsages());
        $this->assertTrue($visitor->hasMetFunctionRequirements());
        $this->assertSame([], $visitor->getMissingRequirements());
    }

    public function testLeaveNodeWithARequiredFunctionIsNotRecorded(): void
    {
        $node = new FuncCall(new Name('file'));
        $visitor = new FunctionVisitor(['file_get_contents'], []);
        $visitor->leaveNode($node);

        $this->assertSame([], $visitor->getRequiredUsages());
        $this->assertFalse($visitor->hasMetFunctionRequirements());
        $this->assertSame(['file_get_contents'], $visitor->getMissingRequirements());
    }

    public function testBannedUsagesAreRecorded(): void
    {
        $node = new FuncCall(new Name('file_get_contents'));
        $visitor = new FunctionVisitor([], ['file_get_contents']);
        $visitor->leaveNode($node);

        $this->assertTrue($visitor->hasUsedBannedFunctions());
        $this->assertSame([$node], $visitor->getBannedUsages());
    }

    public function testBannedUsagesAreNotRecorded(): void
    {
        $node = new FuncCall(new Name('file'));
        $visitor = new FunctionVisitor([], ['file_get_contents']);
        $visitor->leaveNode($node);

        $this->assertFalse($visitor->hasUsedBannedFunctions());
        $this->assertSame([], $visitor->getBannedUsages());
    }

    public function testLeaveNodeWithMultipleRequirements(): void
    {
        $node = new FuncCall(new Name('file'));
        $visitor = new FunctionVisitor(['file', 'file_get_contents'], []);
        $visitor->leaveNode($node);

        $this->assertSame([$node], $visitor->getRequiredUsages());
        $this->assertFalse($visitor->hasMetFunctionRequirements());
        $this->assertSame(['file_get_contents'], $visitor->getMissingRequirements());
    }
}
