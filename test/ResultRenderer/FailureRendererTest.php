<?php

namespace PhpSchool\PhpWorkshopTest\ResultRenderer;

use PhpSchool\PhpWorkshop\Check\CheckInterface;
use PhpSchool\PhpWorkshop\Result\Failure;
use PhpSchool\PhpWorkshop\ResultRenderer\FailureRenderer;

class FailureRendererTest extends AbstractResultRendererTest
{
    public function testRender(): void
    {
        $failure = new Failure($this->createMock(CheckInterface::class), 'Something went wrong');
        $renderer = new FailureRenderer($failure);
        $this->assertEquals("               Something went wrong\n", $renderer->render($this->getRenderer()));
    }
}
