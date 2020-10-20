<?php

namespace PhpSchool\PhpWorkshop\ResultRenderer;

use PhpSchool\PhpWorkshop\Result\Cli\RequestFailure;
use PhpSchool\PhpWorkshop\Result\ComparisonFailure;

/**
 * Renderer for `PhpSchool\PhpWorkshop\Result\ComparisonFailure`.
 */
class ComparisonFailureRenderer implements ResultRendererInterface
{
    /**
     * @var ComparisonFailure
     */
    private $result;

    /**
     * @param ComparisonFailure $result The failure.
     */
    public function __construct(ComparisonFailure $result)
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
            $renderer->style('YOUR OUTPUT:', ['bold', 'yellow']),
            $this->indent($renderer->style(sprintf('"%s"', $this->result->getActualValue()), 'red')),
            $renderer->style('EXPECTED OUTPUT:', ['bold', 'yellow']),
            $this->indent($renderer->style(sprintf('"%s"', $this->result->getExpectedValue()), 'green'))
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
                    return sprintf('  %s', $line);
                },
                explode("\n", $string)
            )
        );
    }
}
