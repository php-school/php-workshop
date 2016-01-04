<?php

namespace PhpSchool\PhpWorkshopTest\ResultRenderer;

use InvalidArgumentException;
use PhpSchool\PhpWorkshop\Check\CheckInterface;
use PhpSchool\PhpWorkshop\Result\CgiOutFailure;
use PhpSchool\PhpWorkshop\Result\CgiOutRequestFailure;
use PhpSchool\PhpWorkshop\Result\CgiOutResult;
use PhpSchool\PhpWorkshop\Result\ResultInterface;
use PhpSchool\PhpWorkshop\Result\Success;
use PhpSchool\PhpWorkshop\ResultRenderer\CgiOutFailureRenderer;
use PhpSchool\PhpWorkshop\ResultRenderer\CgiOutResultRenderer;
use Psr\Http\Message\RequestInterface;

/**
 * Class CgiOutResultRendererTest
 * @package PhpSchool\PhpWorkshopTest\ResultRenderer
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class CgiOutResultRendererTest extends AbstractResultRendererTest
{
    public function testRendererThrowsExceptionIfNotCorrectResult()
    {
        $mock = $this->getMock(ResultInterface::class);
        $this->setExpectedException(
            InvalidArgumentException::class,
            sprintf('Incompatible result type: %s', get_class($mock))
        );
        $renderer = new CgiOutResultRenderer;
        $renderer->render($mock, $this->getRenderer());
    }

    public function testRenderWhenOnlyHeadersDifferent()
    {
        $check = $this->getMock(CheckInterface::class);
        $failure = new CgiOutRequestFailure(
            $check,
            $this->getMock(RequestInterface::class),
            'OUTPUT',
            'OUTPUT',
            ['header1' => 'val', 'header2' => 'val'],
            ['header1' => 'val']
        );
        $result = new CgiOutResult($check, [$failure]);
        $renderer = new CgiOutResultRenderer;

        $expected  = "\n";
        $expected .= "\e[32m\e[4m\e[1mRequest 01\n\n";
        $expected .= "\e[0m\e[0m\e[0m  \e[33m\e[1mACTUAL HEADERS:\e[0m\e[0m    \e[31mheader1: val\e[0m\n\n";
        $expected .= "  \e[33m\e[1mEXPECTED HEADERS:\e[0m\e[0m  \e[39mheader1: val\e[0m\n";
        $expected .= "                     \e[39mheader2: val\e[0m\n\n";
        $expected .= "\e[33m────────────────────\e[0m\n";

        $this->assertSame($expected, $renderer->render($result, $this->getRenderer()));
    }

    public function testRenderWhenOnlyOutputDifferent()
    {
        $check = $this->getMock(CheckInterface::class);
        $failure = new CgiOutRequestFailure(
            $check,
            $this->getMock(RequestInterface::class),
            'EXPECTED OUTPUT',
            'ACTUAL OUTPUT',
            ['header1' => 'val'],
            ['header1' => 'val']
        );
        $result = new CgiOutResult($check, [$failure]);
        $renderer = new CgiOutResultRenderer;

        $expected  = "\n";
        $expected .= "\e[32m\e[4m\e[1mRequest 01\n\n";
        $expected .= "\e[0m\e[0m\e[0m  \e[33m\e[1mACTUAL CONTENT:\e[0m\e[0m    \e[31m\"ACTUAL OUTPUT\"\e[0m\n\n";
        $expected .= "  \e[33m\e[1mEXPECTED CONTENT:\e[0m\e[0m  \e[39m\"EXPECTED OUTPUT\"\e[0m\n";
        $expected .= "\e[33m────────────────────\e[0m\n";

        $this->assertSame($expected, $renderer->render($result, $this->getRenderer()));
    }

    public function testRenderWhenOutputAndHeadersDifferent()
    {
        $check = $this->getMock(CheckInterface::class);
        $failure = new CgiOutRequestFailure(
            $check,
            $this->getMock(RequestInterface::class),
            'EXPECTED OUTPUT',
            'ACTUAL OUTPUT',
            ['header1' => 'val', 'header2' => 'val'],
            ['header1' => 'val']
        );
        $result = new CgiOutResult($check, [$failure]);
        $renderer = new CgiOutResultRenderer;

        $expected  = "\n";
        $expected .= "\e[32m\e[4m\e[1mRequest 01\n\n";
        $expected .= "\e[0m\e[0m\e[0m  \e[33m\e[1mACTUAL HEADERS:\e[0m\e[0m    \e[31mheader1: val\e[0m\n\n";
        $expected .= "  \e[33m\e[1mEXPECTED HEADERS:\e[0m\e[0m  \e[39mheader1: val\e[0m\n";
        $expected .= "                     \e[39mheader2: val\e[0m\n\n";
        $expected .= "\e[1m\e[32m  * * * * * * * * *\n\n";
        $expected .= "\e[0m\e[0m  \e[33m\e[1mACTUAL CONTENT:\e[0m\e[0m    \e[31m\"ACTUAL OUTPUT\"\e[0m\n\n";
        $expected .= "  \e[33m\e[1mEXPECTED CONTENT:\e[0m\e[0m  \e[39m\"EXPECTED OUTPUT\"\e[0m\n";
        $expected .= "\e[33m────────────────────\e[0m\n";

        $this->assertSame($expected, $renderer->render($result, $this->getRenderer()));
    }

    public function testNothingIsRenderedForSuccess()
    {
        $check = $this->getMock(CheckInterface::class);
        $failure = new CgiOutRequestFailure(
            $check,
            $this->getMock(RequestInterface::class),
            'EXPECTED OUTPUT',
            'ACTUAL OUTPUT',
            ['header1' => 'val', 'header2' => 'val'],
            ['header1' => 'val']
        );
        $result = new CgiOutResult($check, [$failure, new Success($check)]);
        $renderer = new CgiOutResultRenderer;

        $expected  = "\n";
        $expected .= "\e[32m\e[4m\e[1mRequest 01\n\n";
        $expected .= "\e[0m\e[0m\e[0m  \e[33m\e[1mACTUAL HEADERS:\e[0m\e[0m    \e[31mheader1: val\e[0m\n\n";
        $expected .= "  \e[33m\e[1mEXPECTED HEADERS:\e[0m\e[0m  \e[39mheader1: val\e[0m\n";
        $expected .= "                     \e[39mheader2: val\e[0m\n\n";
        $expected .= "\e[1m\e[32m  * * * * * * * * *\n\n";
        $expected .= "\e[0m\e[0m  \e[33m\e[1mACTUAL CONTENT:\e[0m\e[0m    \e[31m\"ACTUAL OUTPUT\"\e[0m\n\n";
        $expected .= "  \e[33m\e[1mEXPECTED CONTENT:\e[0m\e[0m  \e[39m\"EXPECTED OUTPUT\"\e[0m\n";
        $expected .= "\e[33m────────────────────\e[0m\n";

        $this->assertSame($expected, $renderer->render($result, $this->getRenderer()));
    }
}
