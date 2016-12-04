<?php

namespace PhpSchool\PhpWorkshopTest\ResultRenderer;

use Colors\Color;
use PhpSchool\CliMenu\Terminal\TerminalInterface;
use PhpSchool\PhpWorkshop\ExerciseRepository;
use PhpSchool\PhpWorkshop\Factory\ResultRendererFactory;
use PhpSchool\PhpWorkshop\Result\Failure;
use PhpSchool\PhpWorkshop\Result\ResultInterface;
use PhpSchool\PhpWorkshop\ResultRenderer\FailureRenderer;
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

        $resultRendererFactory = new ResultRendererFactory;
        $resultRendererFactory->registerRenderer(Failure::class, FailureRenderer::class);

        $terminal = $this->prophesize(TerminalInterface::class);
        $terminal->getWidth()->willReturn(30);

        $renderer = new ResultsRenderer(
            'app',
            $color,
            $terminal->reveal(),
            new ExerciseRepository([]),
            (new Factory)->__invoke(),
            $resultRendererFactory
        );


        $result = new Failure('Failure', 'Some Failure');
        $this->assertSame("         Some Failure\n", $renderer->renderResult($result));
    }

    public function testLineBreak()
    {
        $color = new Color;
        $color->setForceStyle(true);

        $terminal = $this->prophesize(TerminalInterface::class);
        $terminal->getWidth()->willReturn(10);

        $renderer = new ResultsRenderer(
            'app',
            $color,
            $terminal->reveal(),
            new ExerciseRepository([]),
            (new Factory)->__invoke(),
            new ResultRendererFactory
        );

        $this->assertSame("\e[33m──────────\e[0m", $renderer->lineBreak());
    }
}
