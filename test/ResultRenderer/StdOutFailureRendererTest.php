<?php

namespace PhpWorkshop\PhpWorkshopTest\ResultRenderer;

use Colors\Color;
use InvalidArgumentException;
use MikeyMike\CliMenu\Terminal\TerminalInterface;
use PHPUnit_Framework_TestCase;
use PhpWorkshop\PhpWorkshop\ExerciseRepository;
use PhpWorkshop\PhpWorkshop\Result\ResultInterface;
use PhpWorkshop\PhpWorkshop\Result\StdOutFailure;
use PhpWorkshop\PhpWorkshop\ResultRenderer\ResultsRenderer;
use PhpWorkshop\PhpWorkshop\ResultRenderer\StdOutFailureRenderer;

/**
 * Class StdOutFailureRendererTest
 * @package PhpWorkshop\PhpWorkshopTest\ResultRenderer
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class StdOutFailureRendererTest extends PHPUnit_Framework_TestCase
{
    public function testRendererThrowsExceptionIfNotCorrectResult()
    {
        $mock = $this->getMock(ResultInterface::class);
        $this->setExpectedException(
            InvalidArgumentException::class,
            sprintf('Incompatible result type: %s', get_class($mock))
        );
        $renderer = new StdOutFailureRenderer(new Color);
        $renderer->render($mock, $this->getRenderer());
    }

    public function testRender()
    {
        $failure = new StdOutFailure('EXPECTED OUTPUT', 'ACTUAL OUTPUT');
        $color = new Color;
        $color->setForceStyle(true);
        $renderer = new StdOutFailureRenderer($color);

        $expected  = "  [33m[4m[1mACTUAL[0m[0m[0m\n";
        $expected .= "  [31m\"ACTUAL OUTPUT\"[0m\n\n";
        $expected .= "  [4m[1m[33mEXPECTED[0m[0m[0m\n";
        $expected .= "  [31m\"EXPECTED OUTPUT\"[0m\n";

        $this->assertEquals($expected, $renderer->render($failure, $this->getRenderer()));
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
