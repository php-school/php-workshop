<?php

namespace PhpSchool\PhpWorkshop\ResultRenderer\Cli;

use PhpSchool\PhpWorkshop\Result\Cli\RequestFailure;
use PhpSchool\PhpWorkshop\ResultRenderer\ResultRendererInterface;
use PhpSchool\PhpWorkshop\ResultRenderer\ResultsRenderer;

/**
 * Renderer for `PhpSchool\PhpWorkshop\Result\Cli\RequestFailure`.
 *
 * @package PhpSchool\PhpWorkshop\ResultRenderer
 */
class RequestFailureRenderer implements ResultRendererInterface
{

    /**
     * @var RequestFailure
     */
    private $result;

    /**
     * @param RequestFailure $result The failure.
     */
    public function __construct(RequestFailure $result)
    {
        $this->result = $result;
    }

    /**
     * Print the actual and expected output.
     *
     * @param ResultsRenderer $renderer
     * @return string
     */
    public function render(ResultsRenderer $renderer)
    {
        return sprintf(
            "  %s\n%s\n\n  %s\n%s\n",
            $renderer->style("ACTUAL", ['bold', 'underline', 'yellow']),
            $this->indent($renderer->style(sprintf('"%s"', $this->result->getActualOutput()), 'red')),
            $renderer->style("EXPECTED", ['yellow', 'bold', 'underline']),
            $this->indent($renderer->style(sprintf('"%s"', $this->result->getExpectedOutput()), 'red'))
        );
    }

    /**
     * @param string $string
     * @return string
     */
    private function indent($string)
    {
        return implode(
            "\n",
            array_map(
                function ($line) {
                    return sprintf("  %s", $line);
                },
                explode("\n", $string)
            )
        );
    }
}
