<?php

namespace PhpSchool\PhpWorkshop\ResultRenderer;

use PhpSchool\PhpWorkshop\Result\CgiOutRequestFailure;
use PhpSchool\PhpWorkshop\Result\CgiOutResult;
use PhpSchool\PhpWorkshop\Result\FailureInterface;
use PhpSchool\PhpWorkshop\Result\ResultInterface;
use PhpSchool\PhpWorkshop\Result\SuccessInterface;

/**
 * Class CgiOutResultRenderer
 * @package PhpSchool\PhpWorkshop\ResultRenderer
 */
class CgiOutResultRenderer implements ResultRendererInterface
{

    /**
     * @var CgiOutResult
     */
    private $result;

    /**
     * @param CgiOutResult $result
     */
    public function __construct(CgiOutResult $result)
    {
        $this->result = $result;
    }

    /**
     * @param ResultsRenderer $renderer
     * @return string
     */
    public function render(ResultsRenderer $renderer)
    {
        $results = array_filter($this->result->getIterator()->getArrayCopy(), function (ResultInterface $result) {
            return $result instanceof FailureInterface;
        });

        $output = '';
        foreach ($results as $key => $request) {
            
            $output .= "\n";
            $output .= $renderer->style(sprintf("Request %02d\n\n", $key + 1), ['bold', 'underline', 'green']);

            if (!$request instanceof CgiOutRequestFailure) {
                $output .= $renderer->renderResult($request);
                $output .= $renderer->lineBreak();
                continue;
            }

            if ($request->headersDifferent()) {
                $output .= sprintf(
                    "  %s    %s\n  %s  %s\n",
                    $renderer->style("ACTUAL HEADERS:", ['bold', 'yellow']),
                    $this->headers($request->getActualHeaders(), $renderer),
                    $renderer->style("EXPECTED HEADERS:", ['bold', 'yellow']),
                    $this->headers($request->getExpectedHeaders(), $renderer, false)
                );
            }

            if ($request->bodyDifferent()) {
                if ($request->headersAndBodyDifferent()) {
                    $output .= $renderer->style("  * * * * * * * * *\n\n", ['green', 'bold']);
                }

                $output .= sprintf(
                    "  %s    %s\n\n  %s  %s\n",
                    $renderer->style("ACTUAL CONTENT:", ['bold', 'yellow']),
                    $renderer->style(sprintf('"%s"', $request->getActualOutput()), 'red'),
                    $renderer->style("EXPECTED CONTENT:", ['bold', 'yellow']),
                    $renderer->style(sprintf('"%s"', $request->getExpectedOutput()), 'default')
                );
            }

            $output .= $renderer->lineBreak();
        }

        return $output . "\n";
    }

    /**
     * @param array $headers
     * @param ResultsRenderer $renderer
     * @param bool $actual
     * @return string
     */
    private function headers(array $headers, ResultsRenderer $renderer, $actual = true)
    {
        $indent = false;
        $output = '';
        foreach ($headers as $name => $value) {
            if ($indent) {
                $output .= str_repeat(' ', 21);
            }
            
            $output .=  $renderer->style(sprintf("%s: %s", $name, $value), $actual ? 'red' : 'default') . "\n";
            $indent  = true;
        }
        
        return $output;
    }
}
