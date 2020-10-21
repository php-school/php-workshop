<?php

namespace PhpSchool\PhpWorkshop\ResultRenderer;

use PhpSchool\PhpWorkshop\Result\Cli\CliResult;
use PhpSchool\PhpWorkshop\Result\Cli\FailureInterface;
use PhpSchool\PhpWorkshop\Result\Cli\ResultInterface;

/**
 * Renderer for `PhpSchool\PhpWorkshop\Result\Cli\CliResult`.
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
    public function render(ResultsRenderer $renderer): string
    {
        $results = array_filter($this->result->getResults(), function (ResultInterface $result) {
            return $result instanceof FailureInterface;
        });

        $output = '';
        if (count($results)) {
            $output .= $renderer->center("Some executions of your solution produced incorrect output!\n");
        }

        /** @var FailureInterface $request **/
        foreach ($results as $key => $request) {
            $output .= $renderer->lineBreak();
            $output .= "\n";
            $output .= $renderer->style(sprintf('Execution %d', $key + 1), ['bold', 'underline', 'blue']);
            $output .= ' ' . $renderer->style(' FAILED ', ['bg_red', 'bold']) . "\n\n";

            $output .= $request->getArgs()->isEmpty()
                ? "Arguments: None\n"
                : sprintf("Arguments: \"%s\"\n", $request->getArgs()->implode('", "'));

            $output .= "\n" . $renderer->renderResult($request) . "\n";
        }

        return $output;
    }
}
