<?php

namespace PhpWorkshop\PhpWorkshopTest\ResultRenderer;

use Colors\Color;
use InvalidArgumentException;
use PhpWorkshop\PhpWorkshop\Result\Failure;
use PhpWorkshop\PhpWorkshop\Result\ResultInterface;
use PhpWorkshop\PhpWorkshop\ResultRenderer\FailureRenderer;

/**
 * Class FailureRendererTest
 * @package PhpWorkshop\PhpWorkshopTest\ResultRenderer
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
        $failure = new Failure('Check', 'Something went wrong');
        $renderer = new FailureRenderer(new Color);
        $this->assertEquals('Something went wrong', $renderer->render($failure, $this->getRenderer()));
    }
}
