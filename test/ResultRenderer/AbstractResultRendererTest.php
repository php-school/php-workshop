<?php

namespace PhpSchool\PhpWorkshopTest\ResultRenderer;

use Colors\Color;
use Kadet\Highlighter\KeyLighter;
use PhpSchool\CliMenu\Terminal\TerminalInterface;
use PhpSchool\PhpWorkshop\Factory\ResultRendererFactory;
use PHPUnit\Framework\TestCase;
use PhpSchool\PhpWorkshop\ExerciseRepository;
use PhpSchool\PhpWorkshop\ResultRenderer\ResultsRenderer;

/**
 * Class AbstractResultRendererTest
 * @package PhpSchool\PhpWorkshopTest\ResultRenderer
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
abstract class AbstractResultRendererTest extends TestCase
{
    /**
     * @var ResultRendererFactory
     */
    private $resultRendererFactory;

    /**
     * @var ResultsRenderer
     */
    private $renderer;

    /**
     * @return ResultRendererFactory
     */
    public function getResultRendererFactory(): ResultRendererFactory
    {
        if (null === $this->resultRendererFactory) {
            $this->resultRendererFactory = new ResultRendererFactory;
        }

        return $this->resultRendererFactory;
    }

    /**
     * @return ResultsRenderer
     */
    protected function getRenderer(): ResultsRenderer
    {
        if (null === $this->renderer) {
            $color = new Color;
            $color->setForceStyle(true);

            $terminal = $this->prophesize(TerminalInterface::class);
            $terminal->getWidth()->willReturn(50);
            $exerciseRepo = $this->createMock(ExerciseRepository::class);

            $this->renderer = new ResultsRenderer(
                'appName',
                $color,
                $terminal->reveal(),
                $exerciseRepo,
                new KeyLighter,
                $this->getResultRendererFactory()
            );
        }

        return $this->renderer;
    }
}
