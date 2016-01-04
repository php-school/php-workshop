<?php

namespace PhpSchool\PhpWorkshopTest\ResultRenderer;

use Colors\Color;
use PhpSchool\CliMenu\Terminal\TerminalInterface;
use PhpSchool\PhpWorkshop\ExerciseRepository;
use PhpSchool\PhpWorkshop\Result\ResultInterface;
use PhpSchool\PhpWorkshop\ResultRenderer\ResultRendererInterface;
use PhpSchool\PhpWorkshop\ResultRenderer\ResultsRenderer;
use PhpSchool\PSX\Factory;
use PhpSchool\PSX\SyntaxHighlighter;
use PHPUnit_Framework_TestCase;

/**
 * Class ResultsRendererTest
 * @package PhpSchool\PhpWorkshopTest\ResultRenderer
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class ResultsRendererTest extends PHPUnit_Framework_TestCase
{
    public function testRenderIndividualResult()
    {
        $color = new Color;
        $color->setForceStyle(true);

        $renderer = new ResultsRenderer(
            'app',
            $color,
            $this->getMock(TerminalInterface::class),
            new ExerciseRepository([]),
            (new Factory)->__invoke()
        );

        $result = $this->getMock(ResultInterface::class);
        $resultRenderer = $this->getMock(ResultRendererInterface::class);

        $resultRenderer->expects($this->once())
            ->method('render')
            ->with($result, $renderer)
            ->will($this->returnValue('Rendered Result'));

        $renderer->registerRenderer(get_class($result), $resultRenderer);
        $this->assertSame('Rendered Result', $renderer->renderResult($result));
    }

    public function testLineBreak()
    {
        $color = new Color;
        $color->setForceStyle(true);

        $terminal = $this->getMock(TerminalInterface::class);
        $terminal
            ->expects($this->once())
            ->method('getWidth')
            ->will($this->returnValue(10));

        $renderer = new ResultsRenderer(
            'app',
            $color,
            $terminal,
            new ExerciseRepository([]),
            (new Factory)->__invoke()
        );

        $this->assertSame("\e[33m──────────\e[0m", $renderer->lineBreak());
    }
}
