<?php

namespace PhpWorkshop\PhpWorkshopTest\ResultRenderer;

use Colors\Color;
use InvalidArgumentException;
use MikeyMike\CliMenu\Terminal\TerminalInterface;
use PHPUnit_Framework_TestCase;
use PhpWorkshop\PhpWorkshop\ExerciseRepository;
use PhpWorkshop\PhpWorkshop\Result\FunctionRequirementsFailure;
use PhpWorkshop\PhpWorkshop\Result\ResultInterface;
use PhpWorkshop\PhpWorkshop\ResultRenderer\FunctionRequirementsFailureRenderer;
use PhpWorkshop\PhpWorkshop\ResultRenderer\ResultsRenderer;

/**
 * Class FunctionRequirementsFailureRendererTest
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class FunctionRequirementsFailureRendererTest extends PHPUnit_Framework_TestCase
{
    public function testRendererThrowsExceptionIfNotCorrectResult()
    {
        $mock = $this->getMock(ResultInterface::class);
        $this->setExpectedException(
            InvalidArgumentException::class,
            sprintf('Incompatible result type: %s', get_class($mock))
        );
        $renderer = new FunctionRequirementsFailureRenderer(new Color);
        $renderer->render($mock, $this->getRenderer());
    }

    public function testRenderer()
    {
        $failure = new FunctionRequirementsFailure(
            [['function' => 'file', 'line' => 3], ['function' => 'explode', 'line' => 5]],
            ['implode']
        );
        $color = new Color;
        $color->setForceStyle(true);
        $renderer = new FunctionRequirementsFailureRenderer($color);

        $expected  = "  [33m[4m[1mSome functions were used which should not be used in this exercise[0m[0m[0m\n";
        $expected .= "    file on line 3\n";
        $expected .= "    explode on line 5\n";

        $expected .= "  [33m[4m[1mSome function requirements were missing. You should use the functions[0m[0m[0m\n";
        $expected .= "    implode\n";

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
