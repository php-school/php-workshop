<?php

namespace PhpWorkshop\PhpWorkshopTest\ResultRenderer;

use Colors\Color;
use InvalidArgumentException;
use MikeyMike\CliMenu\Terminal\TerminalFactory;
use MikeyMike\CliMenu\Terminal\TerminalInterface;
use PHPUnit_Framework_TestCase;
use PhpWorkshop\PhpWorkshop\ExerciseRepository;
use PhpWorkshop\PhpWorkshop\Result\Failure;
use PhpWorkshop\PhpWorkshop\Result\ResultInterface;
use PhpWorkshop\PhpWorkshop\ResultRenderer\FailureRenderer;
use PhpWorkshop\PhpWorkshop\ResultRenderer\ResultsRenderer;

/**
 * Class FailureRendererTest
 * @package PhpWorkshop\PhpWorkshopTest\ResultRenderer
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class FailureRendererTest extends PHPUnit_Framework_TestCase
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
