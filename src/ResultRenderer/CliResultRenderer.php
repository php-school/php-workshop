<?php

namespace PhpSchool\PhpWorkshop\ResultRenderer;

use PhpSchool\PhpWorkshop\Result\Cli\RequestFailure;
use PhpSchool\PhpWorkshop\Result\CgiOutRequestFailure;
use PhpSchool\PhpWorkshop\Result\Cli\CliResult;
use PhpSchool\PhpWorkshop\Result\Cli\FailureInterface;
use PhpSchool\PhpWorkshop\Result\Cli\ResultInterface;
use PhpSchool\PhpWorkshop\Utils\RequestRenderer;

/**
 * Renderer for `PhpSchool\PhpWorkshop\Result\Cli\CliResult`.
 *
 * @package PhpSchool\PhpWorkshop\ResultRenderer
 */
class CliResultRenderer implements ResultRendererInterface
{

    /**
     * @var CliResult
     */
    private $result;

    /**
     * @param CliResult $result The result.
     */
    public function __construct(CliResult $result)
    {
        $this->result = $result;
    }

    /**
     * Render the details of each failed request including the mismatching headers and body.
     *
     * @param ResultsRenderer $renderer
     * @return string
     */
    public function render(ResultsRenderer $renderer)
    {
        $results = array_filter($this->result->getResults(), function (ResultInterface $result) {
            return $result instanceof FailureInterface;
        });

        $output = '';
        foreach ($results as $key => $request) {
            $output .= $renderer->renderResult($request) . "\n";
        }

        return $output;
    }
}
