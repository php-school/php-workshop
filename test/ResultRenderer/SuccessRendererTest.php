<?php

namespace PhpSchool\PhpWorkshopTest\ResultRenderer;

use Colors\Color;
use InvalidArgumentException;
use PhpSchool\PhpWorkshop\Result\ResultInterface;
use PhpSchool\PhpWorkshop\Result\Success;
use PhpSchool\PhpWorkshop\ResultRenderer\SuccessRenderer;

/**
 * Class SuccessRendererTest
 * @package PhpSchool\PhpWorkshopTest\ResultRenderer
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class SuccessRendererTest extends AbstractResultRendererTest
{
    public function testRendererThrowsExceptionIfNotCorrectResult()
    {
        $mock = $this->getMock(ResultInterface::class);
        $this->setExpectedException(
            InvalidArgumentException::class,
            sprintf('Incompatible result type: %s', get_class($mock))
        );
        $renderer = new SuccessRenderer(new Color);
        $renderer->render($mock, $this->getRenderer());
    }

    public function testRender()
    {
        $success = new Success('Check');
        $renderer = new SuccessRenderer(new Color);
        $this->assertEquals('', $renderer->render($success, $this->getRenderer()));
    }
}
