<?php

namespace PhpSchool\PhpWorkshopTest\ResultRenderer;

use InvalidArgumentException;
use PhpSchool\PhpWorkshop\Result\FunctionRequirementsFailure;
use PhpSchool\PhpWorkshop\Result\ResultInterface;
use PhpSchool\PhpWorkshop\ResultRenderer\FunctionRequirementsFailureRenderer;

/**
 * Class FunctionRequirementsFailureRendererTest
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class FunctionRequirementsFailureRendererTest extends AbstractResultRendererTest
{
    public function testRendererThrowsExceptionIfNotCorrectResult()
    {
        $mock = $this->getMock(ResultInterface::class);
        $this->setExpectedException(
            InvalidArgumentException::class,
            sprintf('Incompatible result type: %s', get_class($mock))
        );
        $renderer = new FunctionRequirementsFailureRenderer;
        $renderer->render($mock, $this->getRenderer());
    }

    public function testRenderer()
    {
        $failure = new FunctionRequirementsFailure(
            [['function' => 'file', 'line' => 3], ['function' => 'explode', 'line' => 5]],
            ['implode']
        );
        $renderer = new FunctionRequirementsFailureRenderer;

        $expected  = "  [33m[4m[1mSome functions were used which should not be used in this exercise[0m[0m[0m\n";
        $expected .= "    file on line 3\n";
        $expected .= "    explode on line 5\n";

        $expected .= "  [33m[4m[1mSome function requirements were missing. You should use the functions[0m[0m[0m";
        $expected .= "\n";
        $expected .= "    implode\n";

        $this->assertEquals($expected, $renderer->render($failure, $this->getRenderer()));
    }
}
