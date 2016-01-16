<?php

namespace PhpSchool\PhpWorkshopTest\ResultRenderer;

use Colors\Color;
use InvalidArgumentException;
use PhpSchool\PhpWorkshop\Check\CheckInterface;
use PhpSchool\PhpWorkshop\Result\Failure;
use PhpSchool\PhpWorkshop\Result\ResultInterface;
use PhpSchool\PhpWorkshop\ResultRenderer\FailureRenderer;

/**
 * Class FailureRendererTest
 * @package PhpSchool\PhpWorkshopTest\ResultRenderer
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class FailureRendererTest extends AbstractResultRendererTest
{
    public function testRendererThrowsExceptionIfNotCorrectResult()
    {
        $mock = $this->getMock(ResultInterface::class);
        $this->setExpectedException(
            InvalidArgumentException::class,
            sprintf('Incompatible result type: %s', get_class($mock))
        );
        $renderer = new FailureRenderer();
        $renderer->render($mock, $this->getRenderer());
    }

    public function testRender()
    {
        $failure = new Failure($this->getMock(CheckInterface::class), 'Something went wrong');
        $renderer = new FailureRenderer(new Color);
        $this->assertEquals("  Something went wrong\n", $renderer->render($failure, $this->getRenderer()));
    }
}
