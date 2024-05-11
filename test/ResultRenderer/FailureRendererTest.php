<?php

namespace PhpSchool\PhpWorkshopTest\ResultRenderer;

use PhpSchool\PhpWorkshop\Result\Failure;
use PhpSchool\PhpWorkshop\ResultRenderer\FailureRenderer;

class FailureRendererTest extends AbstractResultRendererTest
{
    public function testRender(): void
    {
        $failure = new Failure('My check', 'Something went wrong');
        $renderer = new FailureRenderer($failure);
        $this->assertEquals("               Something went wrong\n", $renderer->render($this->getRenderer()));
    }
}
