<?php

namespace PhpSchool\PhpWorkshopTest\ResultRenderer;

use Colors\Color;
use PhpSchool\CliMenu\Terminal\TerminalInterface;
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
     * @return ResultsRenderer
     */
    protected function getRenderer()
    {
        $color = new Color;
        $color->setForceStyle(true);

        $terminal = $this->getMock(TerminalInterface::class);
        $exerciseRepo = $this->getMockBuilder(ExerciseRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $syntaxHighlighter = (new Factory)->__invoke();
        return new ResultsRenderer('appName', $color, $terminal, $exerciseRepo, $syntaxHighlighter);
    }
}
