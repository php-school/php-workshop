<?php

namespace PhpSchool\PhpWorkshopTest\ResultRenderer;

use InvalidArgumentException;
use PhpSchool\PhpWorkshop\Check\CheckInterface;
use PhpSchool\PhpWorkshop\Result\CgiOutFailure;
use PhpSchool\PhpWorkshop\Result\CgiOutRequestFailure;
use PhpSchool\PhpWorkshop\Result\CgiOutResult;
use PhpSchool\PhpWorkshop\Result\Failure;
use PhpSchool\PhpWorkshop\Result\ResultInterface;
use PhpSchool\PhpWorkshop\Result\Success;
use PhpSchool\PhpWorkshop\ResultRenderer\CgiOutFailureRenderer;
use PhpSchool\PhpWorkshop\ResultRenderer\CgiOutResultRenderer;
use PhpSchool\PhpWorkshop\ResultRenderer\FailureRenderer;
use Psr\Http\Message\RequestInterface;

/**
 * Class CgiOutResultRendererTest
 * @package PhpSchool\PhpWorkshopTest\ResultRenderer
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class CgiOutResultRendererTest extends AbstractResultRendererTest
{
    public function testRenderWhenOnlyHeadersDifferent()
    {
        $failure = new CgiOutRequestFailure(
            $this->getMock(RequestInterface::class),
            'OUTPUT',
            'OUTPUT',
            ['header1' => 'val', 'header2' => 'val'],
            ['header1' => 'val']
        );
        $result = new CgiOutResult('Some Check', [$failure]);
        $renderer = new CgiOutResultRenderer($result);

        $expected  = "\n";
        $expected .= "\e[32m\e[4m\e[1mRequest 01\n\n";
        $expected .= "\e[0m\e[0m\e[0m  \e[33m\e[1mACTUAL HEADERS:\e[0m\e[0m    \e[31mheader1: val\e[0m\n\n";
        $expected .= "  \e[33m\e[1mEXPECTED HEADERS:\e[0m\e[0m  \e[39mheader1: val\e[0m\n";
        $expected .= "                     \e[39mheader2: val\e[0m\n\n";
        $expected .= "\e[33m────────────────────\e[0m\n";

        $this->assertSame($expected, $renderer->render($this->getRenderer()));
    }

    public function testRenderWhenOnlyOutputDifferent()
    {
        $failure = new CgiOutRequestFailure(
            $this->getMock(RequestInterface::class),
            'EXPECTED OUTPUT',
            'ACTUAL OUTPUT',
            ['header1' => 'val'],
            ['header1' => 'val']
        );
        $result = new CgiOutResult('Some Check', [$failure]);
        $renderer = new CgiOutResultRenderer($result);

        $expected  = "\n";
        $expected .= "\e[32m\e[4m\e[1mRequest 01\n\n";
        $expected .= "\e[0m\e[0m\e[0m  \e[33m\e[1mACTUAL CONTENT:\e[0m\e[0m    \e[31m\"ACTUAL OUTPUT\"\e[0m\n\n";
        $expected .= "  \e[33m\e[1mEXPECTED CONTENT:\e[0m\e[0m  \e[39m\"EXPECTED OUTPUT\"\e[0m\n";
        $expected .= "\e[33m────────────────────\e[0m\n";

        $this->assertSame($expected, $renderer->render($this->getRenderer()));
    }

    public function testRenderWhenOutputAndHeadersDifferent()
    {
        $failure = new CgiOutRequestFailure(
            $this->getMock(RequestInterface::class),
            'EXPECTED OUTPUT',
            'ACTUAL OUTPUT',
            ['header1' => 'val', 'header2' => 'val'],
            ['header1' => 'val']
        );
        $result = new CgiOutResult('Some Check', [$failure]);
        $renderer = new CgiOutResultRenderer($result);

        $expected  = "\n";
        $expected .= "\e[32m\e[4m\e[1mRequest 01\n\n";
        $expected .= "\e[0m\e[0m\e[0m  \e[33m\e[1mACTUAL HEADERS:\e[0m\e[0m    \e[31mheader1: val\e[0m\n\n";
        $expected .= "  \e[33m\e[1mEXPECTED HEADERS:\e[0m\e[0m  \e[39mheader1: val\e[0m\n";
        $expected .= "                     \e[39mheader2: val\e[0m\n\n";
        $expected .= "\e[1m\e[32m  * * * * * * * * *\n\n";
        $expected .= "\e[0m\e[0m  \e[33m\e[1mACTUAL CONTENT:\e[0m\e[0m    \e[31m\"ACTUAL OUTPUT\"\e[0m\n\n";
        $expected .= "  \e[33m\e[1mEXPECTED CONTENT:\e[0m\e[0m  \e[39m\"EXPECTED OUTPUT\"\e[0m\n";
        $expected .= "\e[33m────────────────────\e[0m\n";

        $this->assertSame($expected, $renderer->render($this->getRenderer()));
    }

    public function testNothingIsRenderedForSuccess()
    {
        $failure = new CgiOutRequestFailure(
            $this->getMock(RequestInterface::class),
            'EXPECTED OUTPUT',
            'ACTUAL OUTPUT',
            ['header1' => 'val', 'header2' => 'val'],
            ['header1' => 'val']
        );
        $result = new CgiOutResult('Some Check', [$failure, new Success('Successful')]);
        $renderer = new CgiOutResultRenderer($result);

        $expected  = "\n";
        $expected .= "\e[32m\e[4m\e[1mRequest 01\n\n";
        $expected .= "\e[0m\e[0m\e[0m  \e[33m\e[1mACTUAL HEADERS:\e[0m\e[0m    \e[31mheader1: val\e[0m\n\n";
        $expected .= "  \e[33m\e[1mEXPECTED HEADERS:\e[0m\e[0m  \e[39mheader1: val\e[0m\n";
        $expected .= "                     \e[39mheader2: val\e[0m\n\n";
        $expected .= "\e[1m\e[32m  * * * * * * * * *\n\n";
        $expected .= "\e[0m\e[0m  \e[33m\e[1mACTUAL CONTENT:\e[0m\e[0m    \e[31m\"ACTUAL OUTPUT\"\e[0m\n\n";
        $expected .= "  \e[33m\e[1mEXPECTED CONTENT:\e[0m\e[0m  \e[39m\"EXPECTED OUTPUT\"\e[0m\n";
        $expected .= "\e[33m────────────────────\e[0m\n";

        $this->assertSame($expected, $renderer->render($this->getRenderer()));
    }

    public function testMultipleFailedRequests()
    {
        $failure1 = new CgiOutRequestFailure(
            $this->getMock(RequestInterface::class),
            'EXPECTED OUTPUT 1',
            'ACTUAL OUTPUT 1',
            ['header1' => 'val', 'header2' => 'val'],
            ['header1' => 'val']
        );

        $failure2 = new CgiOutRequestFailure(
            $this->getMock(RequestInterface::class),
            'EXPECTED OUTPUT 2',
            'ACTUAL OUTPUT 2',
            ['header1' => 'val', 'header2' => 'val'],
            ['header1' => 'val']
        );
        $result = new CgiOutResult('Some Check', [$failure1, $failure2]);
        $renderer = new CgiOutResultRenderer($result);

        $expected  = "\n";
        $expected .= "\e[32m\e[4m\e[1mRequest 01\n\n";
        $expected .= "\e[0m\e[0m\e[0m  \e[33m\e[1mACTUAL HEADERS:\e[0m\e[0m    \e[31mheader1: val\e[0m\n\n";
        $expected .= "  \e[33m\e[1mEXPECTED HEADERS:\e[0m\e[0m  \e[39mheader1: val\e[0m\n";
        $expected .= "                     \e[39mheader2: val\e[0m\n\n";
        $expected .= "\e[1m\e[32m  * * * * * * * * *\n\n";
        $expected .= "\e[0m\e[0m  \e[33m\e[1mACTUAL CONTENT:\e[0m\e[0m    \e[31m\"ACTUAL OUTPUT 1\"\e[0m\n\n";
        $expected .= "  \e[33m\e[1mEXPECTED CONTENT:\e[0m\e[0m  \e[39m\"EXPECTED OUTPUT 1\"\e[0m\n";
        $expected .= "\e[33m────────────────────\e[0m\n";
        $expected .= "\e[32m\e[4m\e[1mRequest 02\n\n";
        $expected .= "\e[0m\e[0m\e[0m  \e[33m\e[1mACTUAL HEADERS:\e[0m\e[0m    \e[31mheader1: val\e[0m\n\n";
        $expected .= "  \e[33m\e[1mEXPECTED HEADERS:\e[0m\e[0m  \e[39mheader1: val\e[0m\n";
        $expected .= "                     \e[39mheader2: val\e[0m\n\n";
        $expected .= "\e[1m\e[32m  * * * * * * * * *\n\n";
        $expected .= "\e[0m\e[0m  \e[33m\e[1mACTUAL CONTENT:\e[0m\e[0m    \e[31m\"ACTUAL OUTPUT 2\"\e[0m\n\n";
        $expected .= "  \e[33m\e[1mEXPECTED CONTENT:\e[0m\e[0m  \e[39m\"EXPECTED OUTPUT 2\"\e[0m\n";
        $expected .= "\e[33m────────────────────\e[0m\n";

        $this->assertSame($expected, $renderer->render($this->getRenderer()));
    }

    public function testCodeExecutionFailureIsDelegatedToMainRenderer()
    {
        $failure = new CgiOutRequestFailure(
            $this->getMock(RequestInterface::class),
            'EXPECTED OUTPUT',
            'ACTUAL OUTPUT',
            ['header1' => 'val', 'header2' => 'val'],
            ['header1' => 'val']
        );

        $codeExecutionFailure = new Failure('Test Check', 'Code Execution Failure');
        $result = new CgiOutResult('Some Check', [$failure, $codeExecutionFailure]);
        $renderer = new CgiOutResultRenderer($result);

        $expected  = "\n";
        $expected .= "\e[32m\e[4m\e[1mRequest 01\n\n";
        $expected .= "\e[0m\e[0m\e[0m  \e[33m\e[1mACTUAL HEADERS:\e[0m\e[0m    \e[31mheader1: val\e[0m\n\n";
        $expected .= "  \e[33m\e[1mEXPECTED HEADERS:\e[0m\e[0m  \e[39mheader1: val\e[0m\n";
        $expected .= "                     \e[39mheader2: val\e[0m\n\n";
        $expected .= "\e[1m\e[32m  * * * * * * * * *\n\n";
        $expected .= "\e[0m\e[0m  \e[33m\e[1mACTUAL CONTENT:\e[0m\e[0m    \e[31m\"ACTUAL OUTPUT\"\e[0m\n\n";
        $expected .= "  \e[33m\e[1mEXPECTED CONTENT:\e[0m\e[0m  \e[39m\"EXPECTED OUTPUT\"\e[0m\n";
        $expected .= "\e[33m────────────────────\e[0m\n";
        $expected .= "\e[32m\e[4m\e[1mRequest 02\n\n";
        $expected .= "\e[0m\e[0m\e[0m  Code Execution Failure\n";
        $expected .= "\e[33m────────────────────\e[0m\n";

        $this->assertSame($expected, $renderer->render($this->getRenderer()));
    }
}
