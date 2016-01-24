<?php

namespace PhpSchool\PhpWorkshopTest\ResultRenderer;

use Colors\Color;
use PhpSchool\CliMenu\Terminal\TerminalInterface;
use PhpSchool\PhpWorkshop\Factory\ResultRendererFactory;
use PhpSchool\PSX\Factory;
use PHPUnit_Framework_TestCase;
use PhpSchool\PhpWorkshop\ExerciseRepository;
use PhpSchool\PhpWorkshop\ResultRenderer\ResultsRenderer;

/**
 * Class AbstractResultRendererTest
 * @package PhpSchool\PhpWorkshopTest\ResultRenderer
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
abstract class AbstractResultRendererTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var TerminalInterface
     */
    protected $terminal;

    /**
     * @return ResultsRenderer
     */
    protected function getRenderer()
    {
        $color = new Color;
        $color->setForceStyle(true);

        $this->terminal = $this->getMock(TerminalInterface::class);
        $exerciseRepo = $this->getMockBuilder(ExerciseRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->terminal
            ->expects($this->any())
            ->method('getWidth')
            ->will($this->returnValue(20));

        $syntaxHighlighter = (new Factory)->__invoke();
        return new ResultsRenderer(
            'appName',
            $color,
            $this->terminal,
            $exerciseRepo,
            $syntaxHighlighter,
            new ResultRendererFactory
        );
    }
}
