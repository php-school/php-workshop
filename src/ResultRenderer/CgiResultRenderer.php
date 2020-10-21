<?php

namespace PhpSchool\PhpWorkshop\ResultRenderer;

use PhpSchool\PhpWorkshop\Result\Cgi\CgiResult;
use PhpSchool\PhpWorkshop\Result\Cgi\FailureInterface;
use PhpSchool\PhpWorkshop\Result\Cgi\ResultInterface;
use PhpSchool\PhpWorkshop\Utils\RequestRenderer;

/**
 * Renderer for `PhpSchool\PhpWorkshop\Result\Cgi\CgiResult`.
 */
class CgiResultRenderer implements ResultRendererInterface
{
    /**
     * @var CgiResult
     */
    private $result;

    /**
     * @var RequestRenderer
     */
    private $requestRenderer;

    /**
     * @param CgiResult $result The result.
     * @param RequestRenderer $requestRenderer
     */
    public function __construct(CgiResult $result, RequestRenderer $requestRenderer)
    {
        $this->result = $result;
        $this->requestRenderer = $requestRenderer;
    }

    /**
     * Render the details of each failed request including the mismatching headers and body.
     *
     * @param ResultsRenderer $renderer
     * @return string
     */
    public function render(ResultsRenderer $renderer): string
    {
        $results = array_filter($this->result->getResults(), function (ResultInterface $result) {
            return $result instanceof FailureInterface;
        });

        $output = '';
        if (count($results)) {
            $output .= $renderer->center("Some requests to your solution produced incorrect output!\n");
        }

        foreach ($results as $key => $request) {
            $output .= $renderer->lineBreak();
            $output .= "\n";
            $output .= $renderer->style(sprintf('Request %d', $key + 1), ['bold', 'underline', 'blue']);
            $output .= ' ' . $renderer->style(' FAILED ', ['bg_red', 'bold']) . "\n\n";
            $output .= "Request Details:\n\n";
            $output .= $this->requestRenderer->renderRequest($request->getRequest()) . "\n";

            $output .= $renderer->renderResult($request) . "\n";
        }

        return $output;
    }
}
