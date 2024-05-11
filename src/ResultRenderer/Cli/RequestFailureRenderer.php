<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\ResultRenderer\Cli;

use PhpSchool\PhpWorkshop\Result\Cli\RequestFailure;
use PhpSchool\PhpWorkshop\ResultRenderer\ResultRendererInterface;
use PhpSchool\PhpWorkshop\ResultRenderer\ResultsRenderer;

/**
 * Renderer for `PhpSchool\PhpWorkshop\Result\Cli\RequestFailure`.
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
    public function render(ResultsRenderer $renderer): string
    {
        return sprintf(
            "  %s\n%s\n\n  %s\n%s\n",
            $renderer->style('YOUR OUTPUT:', ['bold', 'yellow']),
            $this->indent($renderer->style(sprintf('"%s"', $this->result->getActualOutput()), 'red')),
            $renderer->style('EXPECTED OUTPUT:', ['bold', 'yellow']),
            $this->indent($renderer->style(sprintf('"%s"', $this->result->getExpectedOutput()), 'green')),
        );
    }

    /**
     * @param string $string
     * @return string
     */
    private function indent(string $string): string
    {
        return implode(
            "\n",
            array_map(
                function ($line) {
                    return sprintf("  %s", $line);
                },
                explode("\n", $string),
            ),
        );
    }
}
