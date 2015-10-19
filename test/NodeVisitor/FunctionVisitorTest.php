<?php

namespace PhpSchool\PhpWorkshopTest\NodeVisitor;

use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PHPUnit_Framework_TestCase;
use PhpSchool\PhpWorkshop\NodeVisitor\FunctionVisitor;

/**
 * Class FunctionVisitorTest
 * @package PhpSchool\PhpWorkshopTest\NodeVisitor
 * @author  Aydin Hassan <aydin@hotmail.co.uk>
 */
class FunctionVisitorTest extends PHPUnit_Framework_TestCase
{
    public function testLeaveNodeWithARequiredFunctionIsRecorded()
    {
        $node = new FuncCall(new Name('file_get_contents'));
        $visitor = new FunctionVisitor(['file_get_contents'], []);
        $visitor->leaveNode($node);

        $this->assertSame([$node], $visitor->getRequiredUsages());
        $this->assertTrue($visitor->hasMetFunctionRequirements());
        $this->assertSame([], $visitor->getMissingRequirements());
    }

    public function testLeaveNodeWithARequiredFunctionIsNotRecorded()
    {
        $node = new FuncCall(new Name('file'));
        $visitor = new FunctionVisitor(['file_get_contents'], []);
        $visitor->leaveNode($node);

        $this->assertSame([], $visitor->getRequiredUsages());
        $this->assertFalse($visitor->hasMetFunctionRequirements());
        $this->assertSame(['file_get_contents'], $visitor->getMissingRequirements());
    }

    public function testBannedUsagesAreRecorded()
    {
        $node = new FuncCall(new Name('file_get_contents'));
        $visitor = new FunctionVisitor([], ['file_get_contents']);
        $visitor->leaveNode($node);

        $this->assertTrue($visitor->hasUsedBannedFunctions());
        $this->assertSame([$node], $visitor->getBannedUsages());
    }

    public function testBannedUsagesAreNotRecorded()
    {
        $node = new FuncCall(new Name('file'));
        $visitor = new FunctionVisitor([], ['file_get_contents']);
        $visitor->leaveNode($node);

        $this->assertFalse($visitor->hasUsedBannedFunctions());
        $this->assertSame([], $visitor->getBannedUsages());
    }
}
