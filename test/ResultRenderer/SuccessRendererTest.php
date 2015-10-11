<?php

namespace PhpWorkshop\PhpWorkshopTest\ResultRenderer;

use Colors\Color;
use InvalidArgumentException;
use MikeyMike\CliMenu\Terminal\TerminalInterface;
use PHPUnit_Framework_TestCase;
use PhpWorkshop\PhpWorkshop\ExerciseRepository;
use PhpWorkshop\PhpWorkshop\Result\ResultInterface;
use PhpWorkshop\PhpWorkshop\Result\Success;
use PhpWorkshop\PhpWorkshop\ResultRenderer\ResultsRenderer;
use PhpWorkshop\PhpWorkshop\ResultRenderer\SuccessRenderer;

/**
 * Class SuccessRendererTest
 * @package PhpWorkshop\PhpWorkshopTest\ResultRenderer
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class SuccessRendererTest extends PHPUnit_Framework_TestCase
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

    /**
     * @return ResultsRenderer
     */
    private function getRenderer()
    {
        $color = new Color;
        $color->setForceStyle(true);

        $terminal = $this->getMock(TerminalInterface::class);
        $exerciseRepo = $this->getMockBuilder(ExerciseRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        return new ResultsRenderer($color, $terminal, $exerciseRepo);
    }
}
